<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UploadCsvController extends Controller
{
    /**
     * Show the CSV upload form
     */
    public function showUploadForm()
    {
        return view('upload_csv');
    }

    /**
     * Process the uploaded CSV file
     */
    public function processUpload(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            
            // Open and read the CSV file
            $handle = fopen($path, 'r');
            
            if ($handle === false) {
                throw new \Exception('Unable to open the CSV file.');
            }

            // Read the header row
            $header = fgetcsv($handle);
            
            if ($header === false) {
                fclose($handle);
                return redirect()->back()->with('error', 'CSV file is empty or invalid.');
            }

            // Validate required columns
            $requiredColumns = ['meter_number', 'amount', 'disco'];
            $missingColumns = array_diff($requiredColumns, $header);
            
            if (!empty($missingColumns)) {
                fclose($handle);
                return redirect()->back()->with('error', 'Missing required columns: ' . implode(', ', $missingColumns));
            }

            // Process rows
            $rowCount = 0;
            $validRows = [];
            $errors = [];
            $lineNumber = 1; // Start from 1 (header is line 1)

            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Combine header with row data
                $data = array_combine($header, $row);
                
                // Validate row data
                $rowErrors = $this->validateRow($data, $lineNumber);
                
                if (!empty($rowErrors)) {
                    $errors = array_merge($errors, $rowErrors);
                } else {
                    $validRows[] = $data;
                    $rowCount++;
                }
            }

            fclose($handle);

            // Log the upload activity
            Log::info('CSV file uploaded', [
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'valid_rows' => $rowCount,
                'errors' => count($errors)
            ]);

            // If there are errors, show them
            if (!empty($errors)) {
                $errorMessage = 'CSV processed with errors. Valid rows: ' . $rowCount . '. Errors:<br>' . implode('<br>', array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $errorMessage .= '<br>... and ' . (count($errors) - 10) . ' more errors.';
                }
                return redirect()->back()->with('error', $errorMessage);
            }

            // Store the valid rows for processing (in a real implementation, you might save to DB or queue)
            // For now, just return success
            session(['uploaded_csv_data' => $validRows]);

            return redirect()->back()->with('success', "CSV uploaded and processed successfully. Valid rows: {$rowCount}. Ready for batch processing.");

        } catch (\Exception $e) {
            Log::error('CSV upload error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to process CSV: ' . $e->getMessage());
        }
    }

    /**
     * Validate a single row of CSV data
     */
    private function validateRow(array $data, int $lineNumber): array
    {
        $errors = [];

        // Validate meter_number
        if (empty($data['meter_number'])) {
            $errors[] = "Line {$lineNumber}: Meter number is required.";
        } elseif (!preg_match('/^[0-9]{10,15}$/', $data['meter_number'])) {
            $errors[] = "Line {$lineNumber}: Invalid meter number format (must be 10-15 digits).";
        }

        // Validate amount
        if (empty($data['amount'])) {
            $errors[] = "Line {$lineNumber}: Amount is required.";
        } elseif (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors[] = "Line {$lineNumber}: Invalid amount (must be a positive number).";
        }

        // Validate disco - only valid codes allowed
        $validDiscos = ['ABUJA', 'EKO', 'IKEJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH'];
        // Legacy aliases for backward compatibility
        $legacyAliases = ['AEDC', 'EKEDC', 'IKEDC', 'IBEDC', 'EEDC', 'PHED', 'JEDC', 'KAEDCO', 'KEDCO', 'BEDC'];
        $allValidDiscos = array_merge($validDiscos, $legacyAliases);
        
        if (empty($data['disco'])) {
            $errors[] = "Line {$lineNumber}: DISCO is required.";
        } elseif (!in_array(strtoupper($data['disco']), $allValidDiscos)) {
            $errors[] = "Line {$lineNumber}: Invalid DISCO code (must be one of: " . implode(', ', $validDiscos) . ").";
        }

        return $errors;
    }

    /**
     * Download a sample CSV template
     */
    public function downloadTemplate()
    {
        $filename = 'sample_csv_template.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['meter_number', 'amount', 'disco', 'customer_name', 'phone'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add header row
            fputcsv($file, $columns);
            
            // Add sample rows (using valid disco codes)
            fputcsv($file, ['12345678901', '1000', 'EKO', 'John Doe', '08012345678']);
            fputcsv($file, ['98765432109', '2500', 'IKEJA', 'Jane Smith', '08087654321']);
            fputcsv($file, ['55555555555', '5000', 'PH', 'Bob Johnson', '08055555555']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
