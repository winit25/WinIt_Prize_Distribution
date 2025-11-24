<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class CsvStreamReader
{
    /**
     * Stream CSV file and process row by row
     * This avoids loading entire file into memory
     * 
     * @param string $filePath Path to CSV file
     * @param callable $callback Function to call for each row
     * @param int $skipHeader Number of header rows to skip (default 1)
     * @param int $limit Maximum number of rows to process (0 = unlimited)
     * @return array Statistics about the processing
     */
    public static function processStream(
        string $filePath,
        callable $callback,
        int $skipHeader = 1,
        int $limit = 0
    ): array {
        $stats = [
            'total_rows' => 0,
            'processed_rows' => 0,
            'skipped_rows' => 0,
            'errors' => [],
            'header' => []
        ];

        if (!file_exists($filePath)) {
            throw new Exception("CSV file not found at path: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new Exception('CSV file is not readable');
        }

        $fileSize = filesize($filePath);
        Log::info('Starting CSV stream processing', [
            'file_path' => $filePath,
            'file_size' => self::formatBytes($fileSize),
            'skip_header' => $skipHeader,
            'limit' => $limit
        ]);

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception('Unable to open CSV file: ' . (error_get_last()['message'] ?? 'Unknown error'));
        }

        try {
            $rowNumber = 0;
            $processedCount = 0;

            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $rowNumber++;

                // Skip header rows
                if ($rowNumber <= $skipHeader) {
                    if ($rowNumber === $skipHeader) {
                        // Clean and normalize header
                        $stats['header'] = array_map(function ($col) {
                            return strtolower(trim($col, " \t\n\r\0\x0B\xEF\xBB\xBF"));
                        }, $row);
                    }
                    continue;
                }

                $stats['total_rows']++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    $stats['skipped_rows']++;
                    continue;
                }

                // Check limit
                if ($limit > 0 && $processedCount >= $limit) {
                    break;
                }

                try {
                    // Call the callback for this row
                    $result = $callback($row, $rowNumber, $stats['header']);
                    
                    if ($result === false) {
                        // Callback returned false, stop processing
                        break;
                    }
                    
                    $processedCount++;
                    $stats['processed_rows']++;

                } catch (Exception $e) {
                    // Log the error but continue processing
                    $error = [
                        'row' => $rowNumber,
                        'message' => $e->getMessage(),
                        'data' => implode(',', array_slice($row, 0, 3)) . '...'
                    ];
                    $stats['errors'][] = $error;
                    $stats['skipped_rows']++;

                    Log::warning('Error processing CSV row', $error);
                }

                // Log progress every 100 rows
                if ($stats['total_rows'] % 100 === 0) {
                    Log::info('CSV processing progress', [
                        'processed' => $stats['processed_rows'],
                        'total' => $stats['total_rows'],
                        'skipped' => $stats['skipped_rows']
                    ]);
                }
            }

            fclose($handle);

            Log::info('CSV stream processing completed', [
                'total_rows' => $stats['total_rows'],
                'processed_rows' => $stats['processed_rows'],
                'skipped_rows' => $stats['skipped_rows'],
                'errors_count' => count($stats['errors'])
            ]);

            return $stats;

        } catch (Exception $e) {
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Get CSV file header without loading entire file
     */
    public static function getHeader(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("CSV file not found at path: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception('Unable to open CSV file');
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '\\');
            fclose($handle);

            if (!$header) {
                throw new Exception('CSV file is empty');
            }

            // Clean and normalize header
            return array_map(function ($col) {
                return strtolower(trim($col, " \t\n\r\0\x0B\xEF\xBB\xBF"));
            }, $header);

        } catch (Exception $e) {
            fclose($handle);
            throw $e;
        }
    }

    /**
     * Validate CSV structure without loading entire file
     */
    public static function validateStructure(
        string $filePath,
        array $requiredColumns = [],
        int $maxRows = 10
    ): array {
        $validation = [
            'valid' => true,
            'header' => [],
            'errors' => [],
            'warnings' => [],
            'row_count' => 0
        ];

        try {
            $header = self::getHeader($filePath);
            $validation['header'] = $header;

            // Check required columns
            foreach ($requiredColumns as $col) {
                if (!in_array(strtolower($col), $header)) {
                    $validation['valid'] = false;
                    $validation['errors'][] = "Missing required column: {$col}";
                }
            }

            // Check file size
            $fileSize = filesize($filePath);
            if ($fileSize > 5 * 1024 * 1024) { // 5MB
                $validation['warnings'][] = 'File size is large (' . self::formatBytes($fileSize) . '), processing may take time';
            }

            // Count rows
            $handle = fopen($filePath, 'r');
            $rowCount = 0;
            while (fgetcsv($handle, 0, ',', '"', '\\') !== false) {
                $rowCount++;
                if ($rowCount > $maxRows + 1) { // +1 for header
                    break;
                }
            }
            fclose($handle);

            $validation['row_count'] = max(0, $rowCount - 1); // Subtract header row

        } catch (Exception $e) {
            $validation['valid'] = false;
            $validation['errors'][] = $e->getMessage();
        }

        return $validation;
    }

    /**
     * Format bytes to human-readable format
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
