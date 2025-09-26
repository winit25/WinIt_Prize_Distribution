<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::post('/debug-upload', function (Request $request) {
    $response = [
        'has_file' => $request->hasFile('csv_file'),
        'files_count' => count($request->allFiles()),
        'all_files' => array_keys($request->allFiles()),
        'post_data' => $request->all(),
        'headers' => $request->headers->all(),
        'content_type' => $request->header('Content-Type'),
        'php_max_upload' => ini_get('upload_max_filesize'),
        'php_max_post' => ini_get('post_max_size'),
        'storage_path' => storage_path('app/uploads'),
        'storage_exists' => is_dir(storage_path('app/uploads')),
        'storage_writable' => is_writable(storage_path('app')),
    ];
    
    if ($request->hasFile('csv_file')) {
        $file = $request->file('csv_file');
        $response['file_info'] = [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'error' => $file->getError(),
            'is_valid' => $file->isValid(),
            'temp_path' => $file->getPathname(),
        ];
        
        try {
            $filename = time() . '_debug_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $filename);
            $response['storage_result'] = [
                'success' => true,
                'path' => $path,
                'full_path' => storage_path('app/' . $path),
                'file_exists' => file_exists(storage_path('app/' . $path)),
            ];
        } catch (Exception $e) {
            $response['storage_result'] = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    return response()->json($response);
});