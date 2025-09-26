<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestUploadController extends Controller
{
    public function test(Request $request)
    {
        Log::info('Test upload request received', [
            'has_file' => $request->hasFile('csv_file'),
            'files' => $request->allFiles(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);

        if (!$request->hasFile('csv_file')) {
            return response()->json([
                'success' => false,
                'error' => 'No file uploaded',
                'debug' => [
                    'has_file' => $request->hasFile('csv_file'),
                    'all_files' => array_keys($request->allFiles()),
                    'request_data' => $request->all()
                ]
            ]);
        }

        $file = $request->file('csv_file');
        
        $debugInfo = [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'error' => $file->getError(),
            'is_valid' => $file->isValid(),
            'temp_path' => $file->getPathname(),
            'temp_exists' => file_exists($file->getPathname()),
        ];

        Log::info('File debug info', $debugInfo);

        try {
            // Test simple store
            $filename = time() . '_test_' . $file->getClientOriginalName();
            $storedPath = $file->storeAs('uploads', $filename);
            
            $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . $storedPath);
            
            $result = [
                'success' => true,
                'stored_path' => $storedPath,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'debug' => $debugInfo
            ];

            Log::info('File upload test result', $result);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('File upload test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'debug' => $debugInfo
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => $debugInfo
            ]);
        }
    }
}