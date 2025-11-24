<?php

namespace App\Services;

use App\Models\Recipient;
use App\Models\Transaction;
use App\Mail\QuarterlyReportMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuarterlyReportService
{
    /**
     * Generate quarterly reports for all recipients who received tokens
     */
    public function generateQuarterlyReports($quarter = null, $year = null)
    {
        if (!$quarter || !$year) {
            $currentQuarter = $this->getCurrentQuarter();
            $quarter = $currentQuarter['quarter'];
            $year = $currentQuarter['year'];
        }

        $dateRange = $this->getQuarterDateRange($quarter, $year);
        
        Log::info('Generating quarterly reports', [
            'quarter' => $quarter,
            'year' => $year,
            'date_range' => $dateRange
        ]);

        // Get all unique recipients who had successful transactions in this quarter
        $recipients = Transaction::with(['recipient'])
            ->where('status', 'success')
            ->whereBetween('processed_at', [$dateRange['start'], $dateRange['end']])
            ->distinct('recipient_id')
            ->get()
            ->pluck('recipient')
            ->filter()
            ->unique('id');

        $reportsGenerated = 0;
        $reportsFailed = 0;

        foreach ($recipients as $recipient) {
            try {
                $this->generateReportForRecipient($recipient, $quarter, $year, $dateRange);
                $reportsGenerated++;
            } catch (\Exception $e) {
                Log::error('Failed to generate quarterly report for recipient', [
                    'recipient_id' => $recipient->id,
                    'quarter' => $quarter,
                    'year' => $year,
                    'error' => $e->getMessage()
                ]);
                $reportsFailed++;
            }
        }

        Log::info('Quarterly reports generation completed', [
            'quarter' => $quarter,
            'year' => $year,
            'generated' => $reportsGenerated,
            'failed' => $reportsFailed
        ]);

        return [
            'success' => true,
            'generated' => $reportsGenerated,
            'failed' => $reportsFailed,
            'quarter' => $quarter,
            'year' => $year
        ];
    }

    /**
     * Generate report for a single recipient
     */
    protected function generateReportForRecipient($recipient, $quarter, $year, $dateRange)
    {
        // Get all transactions for this recipient in the quarter
        $transactions = Transaction::where('recipient_id', $recipient->id)
            ->where('status', 'success')
            ->whereBetween('processed_at', [$dateRange['start'], $dateRange['end']])
            ->orderBy('processed_at', 'asc')
            ->get();

        if ($transactions->isEmpty()) {
            return;
        }

        // Calculate summary
        $summary = [
            'total_tokens' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'total_units' => $transactions->whereNotNull('units')->sum(function($t) {
                return (float) $t->units;
            }),
            'transactions' => $transactions->map(function($t) {
                return [
                    'date' => $t->processed_at->format('Y-m-d H:i:s'),
                    'token' => $t->token,
                    'units' => $t->units,
                    'amount' => $t->amount,
                    'reference' => $t->buypower_reference,
                ];
            })->toArray(),
            'meter_details' => [
                'meter_number' => $recipient->meter_number,
                'disco' => $recipient->disco,
                'meter_type' => $recipient->meter_type,
                'address' => $recipient->address,
            ],
            'quarter' => $quarter,
            'year' => $year,
        ];

        // Send email report
        if ($recipient->phone_number && filter_var($recipient->phone_number, FILTER_VALIDATE_EMAIL)) {
            // If phone_number is actually an email
            Mail::to($recipient->phone_number)->send(new QuarterlyReportMail($recipient, $summary));
        } else {
            // Try to find email from recipient name or use a default format
            // For now, log it - in production, you'd want proper email addresses
            Log::warning('No valid email found for recipient quarterly report', [
                'recipient_id' => $recipient->id,
                'phone' => $recipient->phone_number
            ]);
        }
    }

    /**
     * Get current quarter
     */
    protected function getCurrentQuarter()
    {
        $month = now()->month;
        $quarter = ceil($month / 3);
        return [
            'quarter' => (int) $quarter,
            'year' => now()->year
        ];
    }

    /**
     * Get date range for a quarter
     */
    protected function getQuarterDateRange($quarter, $year)
    {
        $startMonth = (($quarter - 1) * 3) + 1;
        $endMonth = $quarter * 3;

        return [
            'start' => Carbon::create($year, $startMonth, 1)->startOfDay(),
            'end' => Carbon::create($year, $endMonth, Carbon::create($year, $endMonth)->daysInMonth)->endOfDay()
        ];
    }
}
