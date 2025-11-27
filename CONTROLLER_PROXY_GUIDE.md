# Controller Proxy Guide

This guide explains how to update your Laravel controllers to proxy requests to the backend API instead of handling business logic directly.

## Overview

The frontend controllers should now act as a thin proxy layer between the user interface and the backend API. They:
1. Receive requests from the frontend views
2. Forward them to the backend API using `BackendApiClient`
3. Return the response back to the view

## Usage Pattern

### Before (Monolith)
```php
public function upload(Request $request)
{
    // Direct business logic
    $validated = $request->validate([...]);
    $batch = BatchUpload::create([...]);
    // ... more processing
    return redirect()->back()->with('success', 'Uploaded');
}
```

### After (Proxying to Backend API)
```php
use App\Services\BackendApiClient;

public function upload(Request $request)
{
    $backendApi = app(BackendApiClient::class);
    
    // Forward request to backend API
    $response = $backendApi->upload(
        'bulk-token/upload',
        'csv_file',
        $request->file('csv_file'),
        $request->except('csv_file')
    );
    
    if ($response['success']) {
        return redirect()->back()->with('success', $response['message'] ?? 'Upload successful');
    }
    
    return redirect()->back()->with('error', $response['error'] ?? 'Upload failed');
}
```

## BackendApiClient Methods

### GET Request
```php
$backendApi = app(BackendApiClient::class);
$response = $backendApi->get('bulk-token/status/123', ['include' => 'transactions']);
```

### POST Request
```php
$backendApi = app(BackendApiClient::class);
$response = $backendApi->post('bulk-token/process/123', [
    'priority' => 'high'
]);
```

### File Upload
```php
$backendApi = app(BackendApiClient::class);
$response = $backendApi->upload(
    'bulk-token/upload',           // endpoint
    'csv_file',                     // file key
    $request->file('csv_file'),    // file
    ['type' => 'token']             // additional data
);
```

### File Download
```php
$backendApi = app(BackendApiClient::class);
$response = $backendApi->download('bulk-token/download-report/123');

if ($response) {
    return response($response->body())
        ->header('Content-Type', $response->header('Content-Type'))
        ->header('Content-Disposition', $response->header('Content-Disposition'));
}
```

## Example Controller Updates

### BulkTokenController

**Methods to update:**
- `upload()` - Forward CSV upload to backend
- `processBatch()` - Forward processing request
- `getBatchStatus()` - Fetch status from backend
- `show()` - Fetch batch details from backend
- `history()` - Fetch history from backend
- `transactions()` - Fetch transactions from backend
- `downloadBatchReport()` - Download report from backend

**Example:**
```php
<?php

namespace App\Http\Controllers;

use App\Services\BackendApiClient;
use Illuminate\Http\Request;

class BulkTokenController extends Controller
{
    protected BackendApiClient $backendApi;

    public function __construct(BackendApiClient $backendApi)
    {
        $this->backendApi = $backendApi;
    }

    public function upload(Request $request)
    {
        $response = $this->backendApi->upload(
            'bulk-token/upload',
            'csv_file',
            $request->file('csv_file')
        );

        if ($response['success']) {
            return redirect()->route('bulk-token.index')
                ->with('success', $response['message'] ?? 'Upload successful');
        }

        return back()->with('error', $response['error'] ?? 'Upload failed');
    }

    public function processBatch($batchId)
    {
        $response = $this->backendApi->post("bulk-token/process/{$batchId}");

        if ($response['success']) {
            return response()->json(['success' => true, 'message' => 'Processing started']);
        }

        return response()->json(['success' => false, 'error' => $response['error']], 400);
    }

    public function getBatchStatus($batchId)
    {
        $response = $this->backendApi->get("bulk-token/status/{$batchId}");

        return response()->json($response);
    }

    public function show($batchId)
    {
        $response = $this->backendApi->get("bulk-token/show/{$batchId}");

        if ($response['success']) {
            return view('bulk-token.show', [
                'batch' => $response['batch'] ?? null
            ]);
        }

        return redirect()->route('bulk-token.index')
            ->with('error', $response['error'] ?? 'Batch not found');
    }

    public function history(Request $request)
    {
        $response = $this->backendApi->get('batches/tokens', [
            'page' => $request->get('page', 1),
            'per_page' => 15
        ]);

        if ($response['success']) {
            return view('bulk-token.history', [
                'batches' => $response['batches'] ?? [],
                'pagination' => $response['pagination'] ?? null
            ]);
        }

        return view('bulk-token.history', [
            'batches' => [],
            'error' => $response['error'] ?? 'Failed to load history'
        ]);
    }

    public function downloadBatchReport($batchId)
    {
        $response = $this->backendApi->download("bulk-token/download-report/{$batchId}");

        if ($response) {
            $filename = $response->header('Content-Disposition') ?? "batch-{$batchId}-report.xlsx";
            
            return response($response->body())
                ->header('Content-Type', $response->header('Content-Type'))
                ->header('Content-Disposition', $filename);
        }

        return redirect()->back()->with('error', 'Failed to download report');
    }
}
```

### BulkAirtimeController

Similar pattern - all methods should proxy to backend:
- `upload()` → `POST /api/bulk-airtime/upload`
- `processBatch($id)` → `POST /api/bulk-airtime/process/{id}`
- `getBatchStatus($id)` → `GET /api/bulk-airtime/status/{id}`
- `show($id)` → `GET /api/bulk-airtime/show/{id}`
- `history()` → `GET /api/batches/airtime`

### BulkDstvController

Same pattern as above:
- `upload()` → `POST /api/bulk-dstv/upload`
- `processBatch($id)` → `POST /api/bulk-dstv/process/{id}`
- `getBatchStatus($id)` → `GET /api/bulk-dstv/status/{id}`
- `show($id)` → `GET /api/bulk-dstv/show/{id}`
- `history()` → `GET /api/batches/dstv`
- `downloadBatchReport($id)` → `GET /api/bulk-dstv/download-report/{id}`

### NotificationController

- `index()` → `GET /api/notifications`
- `markAsRead($id)` → `POST /api/notifications/{id}/read`
- `retryTransaction($id)` → `POST /api/notifications/retry-transaction/{id}`

## Controllers That Stay Unchanged

These controllers handle frontend-only concerns and don't need backend API calls:

- **DashboardController** - Renders dashboard view (can aggregate data from backend if needed)
- **ProfileController** - Handles user profile (uses local database)
- **UserController** - Manages users (uses local database)
- **Auth Controllers** - Handle authentication (uses local database)
- **PasswordChangeController** - Password updates (uses local database)
- **DeviceFingerprintController** - Device management (uses local database)
- **ActivityLogController** - Activity logs (uses local database)
- **PermissionController** - Permissions/roles (uses local database)

## Best Practices

1. **Always use dependency injection** for BackendApiClient
   ```php
   public function __construct(BackendApiClient $backendApi)
   {
       $this->backendApi = $backendApi;
   }
   ```

2. **Check response success** before using data
   ```php
   if ($response['success']) {
       // Use $response['data']
   } else {
       // Handle $response['error']
   }
   ```

3. **Maintain user experience** - Show appropriate error messages
   ```php
   return back()->with('error', $response['error'] ?? 'Operation failed');
   ```

4. **Preserve view structure** - Controllers should still return the same views
   ```php
   return view('bulk-token.show', ['batch' => $response['batch']]);
   ```

5. **Handle pagination** - Pass pagination data to views
   ```php
   return view('list', [
       'items' => $response['items'],
       'pagination' => $response['pagination']
   ]);
   ```

## Testing the Integration

1. **Test backend API directly:**
   ```bash
   curl -H "Authorization: Bearer {token}" \
        https://backend.elasticbeanstalk.com/api/health
   ```

2. **Check frontend can reach backend:**
   ```php
   Route::get('/test-backend', function() {
       $api = app(\App\Services\BackendApiClient::class);
       return $api->testConnection();
   });
   ```

3. **Test with actual operations** through the UI

## Migration Checklist

For each controller that handles business logic:

- [ ] Inject `BackendApiClient` in constructor
- [ ] Update each method to use `$this->backendApi` instead of direct logic
- [ ] Ensure proper error handling
- [ ] Test all endpoints work correctly
- [ ] Verify views still receive correct data structure
- [ ] Check file uploads/downloads work properly

## Need Help?

- See `app/Services/BackendApiClient.php` for available methods
- Check backend API routes in backend's `routes/api.php`
- Test backend API endpoints using Postman or curl
- Review `BACKEND_DEPLOYMENT.md` for backend API documentation
