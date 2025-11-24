<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Recipient;
use App\Models\BatchUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Search and filter BuyPower records
     */
    public function search(Request $request)
    {
        $query = $request->get('query', '');
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'meter_number' => $request->get('meter_number'),
            'disco' => $request->get('disco'),
            'recipient_name' => $request->get('recipient_name'),
            'status' => $request->get('status'),
            'type' => $request->get('type', 'transactions'), // transactions, recipients, batches
        ];

        $results = [];
        
        if ($filters['type'] === 'transactions') {
            $results = $this->searchTransactions($query, $filters);
        } elseif ($filters['type'] === 'recipients') {
            $results = $this->searchRecipients($query, $filters);
        } elseif ($filters['type'] === 'batches') {
            $results = $this->searchBatches($query, $filters);
        }

        if ($request->expectsJson()) {
            return response()->json($results);
        }

        return view('search.results', compact('results', 'query', 'filters'));
    }

    /**
     * Search transactions with filters
     */
    protected function searchTransactions(string $query, array $filters)
    {
        $transactionQuery = Transaction::with(['recipient', 'batchUpload']);

        // Text search - sanitize user input to prevent SQL injection
        if (!empty($query)) {
            $sanitizedQuery = $this->sanitizeSearchInput($query);
            $transactionQuery->where(function($q) use ($sanitizedQuery) {
                $q->where('phone_number', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('buypower_reference', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('order_id', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('token', 'like', "%{$sanitizedQuery}%")
                  ->orWhereHas('recipient', function($q) use ($sanitizedQuery) {
                      $q->where('name', 'like', "%{$sanitizedQuery}%")
                        ->orWhere('customer_name', 'like', "%{$sanitizedQuery}%")
                        ->orWhere('phone_number', 'like', "%{$sanitizedQuery}%");
                  });
            });
        }

        // Date filter
        if (!empty($filters['date_from'])) {
            $transactionQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $transactionQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Meter number filter - validate and sanitize
        if (!empty($filters['meter_number'])) {
            $sanitizedMeter = $this->sanitizeSearchInput($filters['meter_number']);
            $transactionQuery->whereHas('recipient', function($q) use ($sanitizedMeter) {
                $q->where('meter_number', 'like', "%{$sanitizedMeter}%");
            });
        }

        // Disco filter - validate enum values
        if (!empty($filters['disco'])) {
            $validDiscos = ['EKO', 'IKEJA', 'ABUJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH'];
            $disco = strtoupper(trim($filters['disco']));
            if (in_array($disco, $validDiscos)) {
                $transactionQuery->whereHas('recipient', function($q) use ($disco) {
                    $q->where('disco', $disco);
                });
            }
        }

        // Recipient name filter - sanitize
        if (!empty($filters['recipient_name'])) {
            $sanitizedName = $this->sanitizeSearchInput($filters['recipient_name']);
            $transactionQuery->whereHas('recipient', function($q) use ($sanitizedName) {
                $q->where('name', 'like', "%{$sanitizedName}%")
                  ->orWhere('customer_name', 'like', "%{$sanitizedName}%");
            });
        }

        // Status filter - validate enum values
        if (!empty($filters['status'])) {
            $validStatuses = ['success', 'failed', 'pending', 'processing'];
            if (in_array($filters['status'], $validStatuses)) {
                $transactionQuery->where('status', $filters['status']);
            }
        }

        return $transactionQuery->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Search recipients with filters
     */
    protected function searchRecipients(string $query, array $filters)
    {
        $recipientQuery = Recipient::with(['batchUpload', 'transaction']);

        // Text search - sanitize user input
        if (!empty($query)) {
            $sanitizedQuery = $this->sanitizeSearchInput($query);
            $recipientQuery->where(function($q) use ($sanitizedQuery) {
                $q->where('name', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('customer_name', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('phone_number', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('meter_number', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('address', 'like', "%{$sanitizedQuery}%");
            });
        }

        // Date filter
        if (!empty($filters['date_from'])) {
            $recipientQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $recipientQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Meter number filter - sanitize
        if (!empty($filters['meter_number'])) {
            $sanitizedMeter = $this->sanitizeSearchInput($filters['meter_number']);
            $recipientQuery->where('meter_number', 'like', "%{$sanitizedMeter}%");
        }

        // Disco filter - validate enum values
        if (!empty($filters['disco'])) {
            $validDiscos = ['EKO', 'IKEJA', 'ABUJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH'];
            $disco = strtoupper(trim($filters['disco']));
            if (in_array($disco, $validDiscos)) {
                $recipientQuery->where('disco', $disco);
            }
        }

        // Recipient name filter - sanitize
        if (!empty($filters['recipient_name'])) {
            $sanitizedName = $this->sanitizeSearchInput($filters['recipient_name']);
            $recipientQuery->where(function($q) use ($sanitizedName) {
                $q->where('name', 'like', "%{$sanitizedName}%")
                  ->orWhere('customer_name', 'like', "%{$sanitizedName}%");
            });
        }

        return $recipientQuery->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Search batches with filters
     */
    protected function searchBatches(string $query, array $filters)
    {
        $batchQuery = BatchUpload::with(['user', 'recipients']);

        // Text search - sanitize user input
        if (!empty($query)) {
            $sanitizedQuery = $this->sanitizeSearchInput($query);
            $batchQuery->where(function($q) use ($sanitizedQuery) {
                $q->where('batch_name', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('filename', 'like', "%{$sanitizedQuery}%")
                  ->orWhere('notes', 'like', "%{$sanitizedQuery}%");
            });
        }

        // Date filter
        if (!empty($filters['date_from'])) {
            $batchQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $batchQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Status filter - validate enum values
        if (!empty($filters['status'])) {
            $validStatuses = ['uploaded', 'processing', 'completed', 'failed'];
            if (in_array($filters['status'], $validStatuses)) {
                $batchQuery->where('status', $filters['status']);
            }
        }

        return $batchQuery->orderBy('created_at', 'desc')->paginate(50);
    }

    /**
     * Sanitize search input to prevent SQL injection
     */
    protected function sanitizeSearchInput(string $input): string
    {
        // Remove SQL injection patterns
        $input = trim($input);
        $input = preg_replace('/[%_]/', '', $input); // Remove LIKE wildcards
        $input = preg_replace('/[^a-zA-Z0-9\s\-@.]/', '', $input); // Allow only alphanumeric, spaces, hyphens, @, and dots
        $input = mb_substr($input, 0, 255); // Limit length
        return $input;
    }

    /**
     * Get filter options (discos, statuses, etc.)
     */
    public function getFilterOptions()
    {
        $discos = Recipient::distinct('disco')
            ->whereNotNull('disco')
            ->pluck('disco')
            ->filter()
            ->sort()
            ->values();

        $statuses = Transaction::distinct('status')
            ->whereNotNull('status')
            ->pluck('status')
            ->filter()
            ->sort()
            ->values();

        return response()->json([
            'discos' => $discos,
            'statuses' => $statuses,
        ]);
    }
}
