<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class CircuitBreakerService
{
    private string $serviceName;
    private int $failureThreshold;
    private int $recoveryTimeout;
    private int $halfOpenMaxCalls;

    public function __construct(string $serviceName = 'buypower', int $failureThreshold = 5, int $recoveryTimeout = 60, int $halfOpenMaxCalls = 3)
    {
        $this->serviceName = $serviceName;
        $this->failureThreshold = $failureThreshold;
        $this->recoveryTimeout = $recoveryTimeout;
        $this->halfOpenMaxCalls = $halfOpenMaxCalls;
    }

    /**
     * Execute a call with circuit breaker protection
     */
    public function execute(callable $callback, array $context = [])
    {
        $state = $this->getState();
        
        switch ($state) {
            case 'closed':
                return $this->executeInClosedState($callback, $context);
            case 'open':
                return $this->executeInOpenState($callback, $context);
            case 'half-open':
                return $this->executeInHalfOpenState($callback, $context);
            default:
                throw new Exception("Unknown circuit breaker state: {$state}");
        }
    }

    /**
     * Execute in closed state (normal operation)
     */
    private function executeInClosedState(callable $callback, array $context)
    {
        try {
            $result = $callback();
            $this->recordSuccess();
            return $result;
        } catch (Exception $e) {
            $this->recordFailure($e, $context);
            throw $e;
        }
    }

    /**
     * Execute in open state (circuit is open)
     */
    private function executeInOpenState(callable $callback, array $context)
    {
        $lastFailureTime = Cache::get("circuit_breaker_{$this->serviceName}_last_failure_time");
        
        if (time() - $lastFailureTime >= $this->recoveryTimeout) {
            // Move to half-open state
            $this->setState('half-open');
            Cache::put("circuit_breaker_{$this->serviceName}_half_open_calls", 0, 60);
            
            Log::info("Circuit breaker moving to half-open state", [
                'service' => $this->serviceName,
                'recovery_timeout' => $this->recoveryTimeout
            ]);
            
            return $this->executeInHalfOpenState($callback, $context);
        }
        
        // Circuit is still open, throw exception
        throw new Exception("Circuit breaker is open for {$this->serviceName}. Service unavailable.");
    }

    /**
     * Execute in half-open state (testing recovery)
     */
    private function executeInHalfOpenState(callable $callback, array $context)
    {
        $halfOpenCalls = Cache::get("circuit_breaker_{$this->serviceName}_half_open_calls", 0);
        
        if ($halfOpenCalls >= $this->halfOpenMaxCalls) {
            // Too many calls in half-open state, go back to open
            $this->setState('open');
            Cache::put("circuit_breaker_{$this->serviceName}_last_failure_time", time(), 3600);
            
            Log::warning("Circuit breaker moving back to open state", [
                'service' => $this->serviceName,
                'half_open_calls' => $halfOpenCalls
            ]);
            
            throw new Exception("Circuit breaker is open for {$this->serviceName}. Service unavailable.");
        }
        
        try {
            $result = $callback();
            $this->recordSuccess();
            
            // If successful, move back to closed state
            $this->setState('closed');
            Cache::forget("circuit_breaker_{$this->serviceName}_half_open_calls");
            
            Log::info("Circuit breaker moving to closed state", [
                'service' => $this->serviceName
            ]);
            
            return $result;
        } catch (Exception $e) {
            $this->recordFailure($e, $context);
            
            // Move back to open state
            $this->setState('open');
            Cache::put("circuit_breaker_{$this->serviceName}_last_failure_time", time(), 3600);
            
            Log::warning("Circuit breaker moving back to open state after failure", [
                'service' => $this->serviceName,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Record a successful call
     */
    private function recordSuccess(): void
    {
        Cache::forget("circuit_breaker_{$this->serviceName}_failure_count");
        Cache::forget("circuit_breaker_{$this->serviceName}_last_failure_time");
        
        Log::debug("Circuit breaker recorded success", [
            'service' => $this->serviceName
        ]);
    }

    /**
     * Record a failed call
     */
    private function recordFailure(Exception $exception, array $context): void
    {
        $failureCount = Cache::get("circuit_breaker_{$this->serviceName}_failure_count", 0) + 1;
        Cache::put("circuit_breaker_{$this->serviceName}_failure_count", $failureCount, 3600);
        Cache::put("circuit_breaker_{$this->serviceName}_last_failure_time", time(), 3600);
        
        Log::warning("Circuit breaker recorded failure", [
            'service' => $this->serviceName,
            'failure_count' => $failureCount,
            'error' => $exception->getMessage(),
            'context' => $context
        ]);
        
        if ($failureCount >= $this->failureThreshold) {
            $this->setState('open');
            
            Log::error("Circuit breaker opened due to failure threshold", [
                'service' => $this->serviceName,
                'failure_count' => $failureCount,
                'threshold' => $this->failureThreshold
            ]);
        }
    }

    /**
     * Get current circuit breaker state
     */
    public function getState(): string
    {
        return Cache::get("circuit_breaker_{$this->serviceName}_state", 'closed');
    }

    /**
     * Set circuit breaker state
     */
    private function setState(string $state): void
    {
        Cache::put("circuit_breaker_{$this->serviceName}_state", $state, 3600);
    }

    /**
     * Get circuit breaker statistics
     */
    public function getStats(): array
    {
        return [
            'state' => $this->getState(),
            'failure_count' => Cache::get("circuit_breaker_{$this->serviceName}_failure_count", 0),
            'last_failure_time' => Cache::get("circuit_breaker_{$this->serviceName}_last_failure_time"),
            'half_open_calls' => Cache::get("circuit_breaker_{$this->serviceName}_half_open_calls", 0),
            'failure_threshold' => $this->failureThreshold,
            'recovery_timeout' => $this->recoveryTimeout,
            'half_open_max_calls' => $this->halfOpenMaxCalls
        ];
    }

    /**
     * Reset circuit breaker
     */
    public function reset(): void
    {
        Cache::forget("circuit_breaker_{$this->serviceName}_state");
        Cache::forget("circuit_breaker_{$this->serviceName}_failure_count");
        Cache::forget("circuit_breaker_{$this->serviceName}_last_failure_time");
        Cache::forget("circuit_breaker_{$this->serviceName}_half_open_calls");
        
        Log::info("Circuit breaker reset", [
            'service' => $this->serviceName
        ]);
    }

    /**
     * Check if circuit breaker is available
     */
    public function isAvailable(): bool
    {
        $state = $this->getState();
        
        if ($state === 'closed') {
            return true;
        }
        
        if ($state === 'half-open') {
            $halfOpenCalls = Cache::get("circuit_breaker_{$this->serviceName}_half_open_calls", 0);
            return $halfOpenCalls < $this->halfOpenMaxCalls;
        }
        
        if ($state === 'open') {
            $lastFailureTime = Cache::get("circuit_breaker_{$this->serviceName}_last_failure_time");
            return time() - $lastFailureTime >= $this->recoveryTimeout;
        }
        
        return false;
    }
}
