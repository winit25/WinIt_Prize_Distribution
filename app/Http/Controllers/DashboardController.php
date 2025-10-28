<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatchUpload;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache dashboard statistics for 2 minutes to improve performance
        $cacheKey = 'dashboard_stats_' . auth()->id();
        
        $stats = Cache::remember($cacheKey, 120, function () {
            return [
                'totalBatches' => BatchUpload::count(),
                'totalRecipients' => Recipient::count(),
                'totalTransactions' => Transaction::count(),
                'totalAmount' => Transaction::sum('amount'),
                'successfulTransactions' => Transaction::where('status', 'success')->count(),
                'processingCount' => Transaction::where('status', 'processing')->count(),
                'failedCount' => Transaction::where('status', 'failed')->count(),
            ];
        });
        
        // Calculate success rate
        $successRate = $stats['totalTransactions'] > 0 ? 
            round(($stats['successfulTransactions'] / $stats['totalTransactions']) * 100, 1) : 0;
        
        // Cache recent data for 1 minute
        $recentData = Cache::remember('dashboard_recent_' . auth()->id(), 60, function () {
            return [
                'recentBatches' => BatchUpload::with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
                'recentActivity' => ActivityLog::with('causer')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
            ];
        });

        // Cache chart data for 5 minutes
        $chartData = Cache::remember('dashboard_chart_' . auth()->id(), 300, function () {
            // Get transaction data for the last 7 days
            $last7Days = collect(range(6, 0))->map(function ($days) {
                return now()->subDays($days)->format('Y-m-d');
            });

            $dailyTransactions = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total_amount')
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            $dailyAmounts = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total_amount')
                ->where('created_at', '>=', now()->subDays(6))
                ->groupBy('date')
                ->pluck('total_amount', 'date')
                ->toArray();

            // Get status distribution
            $statusDistribution = Transaction::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Get disco distribution
            $discoDistribution = Transaction::join('recipients', 'transactions.recipient_id', '=', 'recipients.id')
                ->selectRaw('recipients.disco, COUNT(*) as count')
                ->groupBy('recipients.disco')
                ->pluck('count', 'disco')
                ->toArray();

            return [
                'dailyTransactions' => $last7Days->map(function ($date) use ($dailyTransactions) {
                    return $dailyTransactions[$date] ?? 0;
                })->values()->toArray(),
                'dailyAmounts' => $last7Days->map(function ($date) use ($dailyAmounts) {
                    return $dailyAmounts[$date] ?? 0;
                })->values()->toArray(),
                'labels' => $last7Days->map(function ($date) {
                    return now()->parse($date)->format('M d');
                })->values()->toArray(),
                'statusDistribution' => $statusDistribution,
                'discoDistribution' => $discoDistribution
            ];
        });

        return view('dashboard', array_merge($stats, [
            'successRate' => $successRate,
            'recentBatches' => $recentData['recentBatches'],
            'recentActivity' => $recentData['recentActivity'],
            'chartData' => $chartData
        ]));
    }
}
