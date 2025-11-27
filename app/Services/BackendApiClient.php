<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BackendApiClient
{
    protected string $baseUrl;
    protected int $timeout = 30;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.backend_api.url'), '/');
    }

    /**
     * Get authentication token for current user
     */
    protected function getAuthToken(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        // Get or create API token for the user
        $user = Auth::user();
        
        // Check if user has an existing token
        $token = $user->tokens()->where('name', 'backend-api-token')->first();
        
        if (!$token) {
            // Create new token
            $token = $user->createToken('backend-api-token');
            return $token->plainTextToken;
        }

        // Return existing token (Note: plainTextToken only available on creation)
        // For subsequent requests, we'll need to store it in session or regenerate
        return session('backend_api_token') ?? $this->regenerateToken();
    }

    /**
     * Regenerate API token
     */
    protected function regenerateToken(): string
    {
        $user = Auth::user();
        
        // Delete old tokens
        $user->tokens()->where('name', 'backend-api-token')->delete();
        
        // Create new token
        $token = $user->createToken('backend-api-token');
        $plainTextToken = $token->plainTextToken;
        
        // Store in session
        session(['backend_api_token' => $plainTextToken]);
        
        return $plainTextToken;
    }

    /**
     * Make GET request to backend API
     */
    public function get(string $endpoint, array $query = [])
    {
        $token = $this->getAuthToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->get("{$this->baseUrl}/api/{$endpoint}", $query);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Backend API GET request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to connect to backend API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Make POST request to backend API
     */
    public function post(string $endpoint, array $data = [])
    {
        $token = $this->getAuthToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->post("{$this->baseUrl}/api/{$endpoint}", $data);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Backend API POST request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to connect to backend API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload file to backend API
     */
    public function upload(string $endpoint, string $fileKey, $file, array $data = [])
    {
        $token = $this->getAuthToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->attach($fileKey, file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("{$this->baseUrl}/api/{$endpoint}", $data);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Backend API upload request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to upload to backend API: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Download file from backend API
     */
    public function download(string $endpoint)
    {
        $token = $this->getAuthToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->get("{$this->baseUrl}/api/{$endpoint}");

            if ($response->successful()) {
                return $response;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Backend API download request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Handle API response
     */
    protected function handleResponse($response): array
    {
        if ($response->successful()) {
            return array_merge(
                ['success' => true],
                $response->json() ?? []
            );
        }

        // Handle errors
        $error = $response->json('message') ?? $response->json('error') ?? 'Unknown error';
        
        Log::warning('Backend API returned error', [
            'status' => $response->status(),
            'error' => $error,
            'body' => $response->body()
        ]);

        return [
            'success' => false,
            'error' => $error,
            'status' => $response->status()
        ];
    }

    /**
     * Test backend API connection
     */
    public function testConnection(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Backend API is reachable',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Backend API returned status: ' . $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Cannot reach backend API: ' . $e->getMessage()
            ];
        }
    }
}
