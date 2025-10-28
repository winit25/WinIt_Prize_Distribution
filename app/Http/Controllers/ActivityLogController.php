<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Filter by user if specified
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', \App\Models\User::class);
        }

        // Filter by event type if specified
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Filter by date range if specified
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Cache the results for 5 minutes
        $cacheKey = 'activity_logs_' . md5(serialize($request->all()));
        $logs = Cache::remember($cacheKey, 300, function () use ($query) {
            return $query->paginate(50);
        });

        // Get filter options
        $users = Cache::remember('activity_log_users', 300, function () {
            return \App\Models\User::select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        });

        $events = Cache::remember('activity_log_events', 300, function () {
            return ActivityLog::select('event')
                ->distinct()
                ->orderBy('event')
                ->pluck('event');
        });

        return view('admin.activity-logs.index', compact('logs', 'users', 'events'));
    }

    /**
     * Display the specified activity log
     */
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['causer', 'subject']);
        
        return view('admin.activity-logs.show', compact('activityLog'));
    }

    /**
     * Clear old activity logs
     */
    public function clear(Request $request)
    {
        $days = $request->input('days', 30);
        
        $deletedCount = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
        
        // Clear related caches
        Cache::forget('activity_logs_*');
        Cache::forget('activity_log_users');
        Cache::forget('activity_log_events');
        
        return redirect()->route('activity-logs.index')
            ->with('success', "Cleared {$deletedCount} activity logs older than {$days} days.");
    }
}
