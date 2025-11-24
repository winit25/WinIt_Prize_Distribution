<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ActivityLoggingService
{
    /**
     * Get the real IP address of the user, handling proxies and load balancers
     * Checks various headers to get the actual client IP behind proxies/load balancers
     */
    private function getUserIpAddress(): string
    {
        $request = request();
        
        // Check for IP in various headers (for proxies/load balancers)
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx proxy
            'HTTP_X_FORWARDED_FOR',      // Standard proxy header
            'HTTP_X_FORWARDED',          // Alternative proxy header
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_CLIENT_IP',            // Client IP
        ];
        
        foreach ($ipHeaders as $header) {
            $ip = $request->server($header);
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                // If X-Forwarded-For contains multiple IPs, get the first one
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to Laravel's request()->ip() which handles most cases
        $ip = $request->ip();
        
        // Final fallback
        return $ip ?: '0.0.0.0';
    }

    /**
     * Log an activity with proper causer and subject tracking
     * 
     * @param string $action The action name (e.g., 'batch_created', 'user_login')
     * @param string $description Human-readable description
     * @param Model|null $subject The model being acted upon (optional)
     * @param array $properties Additional properties to store
     * @param Model|null $causer The user/model causing the action (defaults to authenticated user)
     * @return ActivityLog
     */
    public function log(string $action, string $description, ?Model $subject = null, array $properties = [], ?Model $causer = null): ActivityLog
    {
        $causer = $causer ?? Auth::user();
        
        // Get user's IP address (handles proxies/load balancers)
        $ipAddress = $this->getUserIpAddress();
        
        return ActivityLog::create([
            'action' => $action,
            'event' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->id : null,
            'properties' => $properties,
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent() ?? 'Unknown',
        ]);
    }

    public function logBatchCreated($batch, ?\App\Models\User $user = null): ActivityLog
    {
        $user = $user ?? Auth::user();
        return $this->log(
            'batch_created',
            "Created batch: {$batch->batch_name}",
            $batch,
            [
                'batch_name' => $batch->batch_name,
                'total_recipients' => $batch->total_recipients,
                'total_amount' => $batch->total_amount,
                'batch_id' => $batch->id,
            ],
            $user
        );
    }

    public function logBatchStatusChanged($batch, string $oldStatus, string $newStatus, ?\App\Models\User $user = null): ActivityLog
    {
        $user = $user ?? Auth::user() ?? $batch->user;
        return $this->log(
            'batch_status_changed',
            "Batch '{$batch->batch_name}' status changed from {$oldStatus} to {$newStatus}",
            $batch,
            [
                'batch_name' => $batch->batch_name,
                'batch_id' => $batch->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'total_recipients' => $batch->total_recipients ?? null,
                'processed_recipients' => $batch->processed_recipients ?? null,
            ],
            $user
        );
    }

    public function logTransactionCreated($transaction): ActivityLog
    {
        $recipientName = 'Unknown';
        if ($transaction->relationLoaded('recipient') && $transaction->recipient) {
            $recipientName = $transaction->recipient->name;
        } elseif (!$transaction->relationLoaded('recipient')) {
            $transaction->load('recipient');
            $recipientName = $transaction->recipient ? $transaction->recipient->name : 'Unknown';
        }
        
        return $this->log(
            'transaction_created',
            "Transaction created for {$recipientName}",
            $transaction,
            [
                'recipient_name' => $recipientName,
                'phone_number' => $transaction->phone_number,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
            ]
        );
    }

    public function logTransactionStatusChanged($transaction, string $oldStatus, string $newStatus): ActivityLog
    {
        $recipientName = 'Unknown';
        if ($transaction->relationLoaded('recipient') && $transaction->recipient) {
            $recipientName = $transaction->recipient->name;
        } elseif (!$transaction->relationLoaded('recipient')) {
            $transaction->load('recipient');
            $recipientName = $transaction->recipient ? $transaction->recipient->name : 'Unknown';
        }
        
        return $this->log(
            'transaction_status_changed',
            "Transaction status changed from {$oldStatus} to {$newStatus}",
            $transaction,
            [
                'recipient_name' => $recipientName,
                'phone_number' => $transaction->phone_number,
                'amount' => $transaction->amount,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );
    }

    public function logTokenGenerated($transaction): ActivityLog
    {
        $recipientName = 'Unknown';
        if ($transaction->relationLoaded('recipient') && $transaction->recipient) {
            $recipientName = $transaction->recipient->name;
        } elseif (!$transaction->relationLoaded('recipient')) {
            $transaction->load('recipient');
            $recipientName = $transaction->recipient ? $transaction->recipient->name : 'Unknown';
        }
        
        return $this->log(
            'token_generated',
            "Token generated for transaction",
            $transaction,
            [
                'recipient_name' => $recipientName,
                'phone_number' => $transaction->phone_number,
                'amount' => $transaction->amount,
                'token' => $transaction->token,
            ]
        );
    }

    public function logUserCreated($user): ActivityLog
    {
        return $this->log(
            'user_created',
            "User created: {$user->name} ({$user->email})",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'created_by' => Auth::id(),
            ]
        );
    }

    public function logUserLogin($user): ActivityLog
    {
        return $this->log(
            'user_login',
            "User logged in: {$user->name} ({$user->email})",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'login_time' => now()->toISOString(),
                'ip_address' => request()->ip(),
            ],
            $user // The user is both causer and subject
        );
    }

    public function logUserLogout($user): ActivityLog
    {
        return $this->log(
            'user_logout',
            "User logged out: {$user->name} ({$user->email})",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'logout_time' => now()->toISOString(),
                'ip_address' => request()->ip(),
            ],
            $user // The user is both causer and subject
        );
    }

    public function logPasswordChanged($user): ActivityLog
    {
        return $this->log(
            'password_changed',
            "Password changed for user: {$user->name}",
            $user,
            [
                'name' => $user->name,
                'email' => $user->email,
                'changed_by' => Auth::id(),
            ]
        );
    }

    public function logApiCall(string $endpoint, array $requestData, array $responseData, bool $success = true): ActivityLog
    {
        return $this->log(
            'api_call',
            "API call to {$endpoint} - " . ($success ? 'Success' : 'Failed'),
            null,
            [
                'endpoint' => $endpoint,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'success' => $success,
            ]
        );
    }

    public function logError(string $error, ?Model $model = null, array $context = []): ActivityLog
    {
        return $this->log(
            'error',
            "Error occurred: {$error}",
            $model,
            array_merge($context, [
                'error_message' => $error,
                'error_time' => now()->toISOString(),
            ])
        );
    }

    public function getActivityLogs(int $limit = 50, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = ActivityLog::with('user')->latest();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($limit);
    }
}
