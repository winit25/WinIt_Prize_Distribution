<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class SharePointService
{
    protected $clientId;
    protected $clientSecret;
    protected $tenantId;
    protected $baseUrl = 'https://graph.microsoft.com/v1.0';

    public function __construct()
    {
        $this->clientId = config('sharepoint.client_id');
        $this->clientSecret = config('sharepoint.client_secret');
        $this->tenantId = config('sharepoint.tenant_id');
    }

    /**
     * Get access token using client credentials flow
     */
    protected function getAccessToken(): string
    {
        $tokenCacheKey = 'sharepoint_access_token';
        
        // Check if we have a cached token
        if (cache()->has($tokenCacheKey)) {
            return cache()->get($tokenCacheKey);
        }

        $tokenUrl = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        
        $response = Http::asForm()->post($tokenUrl, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials',
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to get SharePoint access token: ' . $response->body());
        }

        $data = $response->json();
        $accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600;

        // Cache token for slightly less than expiry time
        cache()->put($tokenCacheKey, $accessToken, now()->addSeconds($expiresIn - 60));

        return $accessToken;
    }

    /**
     * Download file from SharePoint by file URL or file ID
     */
    public function downloadFile(string $fileUrlOrId, ?string $siteId = null, ?string $driveId = null): array
    {
        try {
            $accessToken = $this->getAccessToken();

            // Determine if it's a URL or ID
            if (filter_var($fileUrlOrId, FILTER_VALIDATE_URL)) {
                // It's a URL - extract file ID and download
                $fileId = $this->extractFileIdFromUrl($fileUrlOrId);
                $downloadUrl = $this->buildDownloadUrl($fileId, $siteId, $driveId);
            } else {
                // It's a file ID
                $downloadUrl = $this->buildDownloadUrl($fileUrlOrId, $siteId, $driveId);
            }

            // Download the file
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Accept' => 'application/octet-stream',
                ])
                ->get($downloadUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to download file from SharePoint: ' . $response->body());
            }

            // Get filename from Content-Disposition header or use default
            $filename = $this->extractFilenameFromHeaders($response->headers()) ?? 'sharepoint_file_' . time() . '.csv';
            
            // Store file temporarily
            $tempPath = storage_path('app/temp/' . $filename);
            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            file_put_contents($tempPath, $response->body());

            Log::info('File downloaded from SharePoint', [
                'filename' => $filename,
                'size' => filesize($tempPath),
            ]);

            return [
                'success' => true,
                'file_path' => $tempPath,
                'filename' => $filename,
                'size' => filesize($tempPath),
            ];

        } catch (Exception $e) {
            Log::error('SharePoint download error', [
                'error' => $e->getMessage(),
                'file_url_or_id' => $fileUrlOrId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Download file from SharePoint by sharing link
     */
    public function downloadFromSharingLink(string $sharingLink): array
    {
        try {
            // Extract file ID from sharing link
            $fileId = $this->extractFileIdFromSharingLink($sharingLink);
            
            if (!$fileId) {
                throw new Exception('Could not extract file ID from sharing link');
            }

            return $this->downloadFile($fileId);

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extract file ID from SharePoint URL
     */
    protected function extractFileIdFromUrl(string $url): ?string
    {
        // Handle different SharePoint URL formats
        // Format 1: https://[tenant].sharepoint.com/sites/[site]/[drive]/[path]?id=...
        if (preg_match('/[?&]id=([a-zA-Z0-9%_-]+)/', $url, $matches)) {
            return urldecode($matches[1]);
        }

        // Format 2: https://graph.microsoft.com/v1.0/sites/[site-id]/drives/[drive-id]/items/[item-id]
        if (preg_match('/\/items\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Format 3: Direct file ID in path
        if (preg_match('/\/([a-zA-Z0-9_-]{32,})\//', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract file ID from sharing link
     */
    protected function extractFileIdFromSharingLink(string $sharingLink): ?string
    {
        // SharePoint sharing links format: https://[tenant].sharepoint.com/:x:/g/[encoded-path]?e=[token]
        // We need to decode and extract the file ID
        
        // Try to extract from URL parameters
        if (preg_match('/[?&]id=([a-zA-Z0-9%_-]+)/', $sharingLink, $matches)) {
            return urldecode($matches[1]);
        }

        // For sharing links, we might need to resolve them first
        // This is a simplified version - full implementation would use Graph API
        return null;
    }

    /**
     * Build download URL for file
     */
    protected function buildDownloadUrl(string $fileId, ?string $siteId = null, ?string $driveId = null): string
    {
        if ($siteId && $driveId) {
            return "{$this->baseUrl}/sites/{$siteId}/drives/{$driveId}/items/{$fileId}/content";
        }

        // Try to use the file ID directly
        return "{$this->baseUrl}/me/drive/items/{$fileId}/content";
    }

    /**
     * Extract filename from response headers
     */
    protected function extractFilenameFromHeaders(array $headers): ?string
    {
        $contentDisposition = $headers['Content-Disposition'][0] ?? null;
        
        if ($contentDisposition && preg_match('/filename[^;=\n]*=(([\'"]).*?\2|[^;\n]*)/', $contentDisposition, $matches)) {
            $filename = $matches[1];
            // Remove quotes if present
            $filename = trim($filename, '"\'');
            return $filename;
        }

        return null;
    }

    /**
     * List files in SharePoint folder (optional helper method)
     */
    public function listFiles(string $folderPath = '/', ?string $siteId = null, ?string $driveId = null): array
    {
        try {
            $accessToken = $this->getAccessToken();

            if ($siteId && $driveId) {
                $url = "{$this->baseUrl}/sites/{$siteId}/drives/{$driveId}/root:/{$folderPath}:/children";
            } else {
                $url = "{$this->baseUrl}/me/drive/root:/{$folderPath}:/children";
            }

            $response = Http::withToken($accessToken)->get($url);

            if (!$response->successful()) {
                throw new Exception('Failed to list files: ' . $response->body());
            }

            return [
                'success' => true,
                'files' => $response->json()['value'] ?? [],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

