<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs
     * Shows all activities for users with 'view-activity-logs' permission
     * Regular users only see their own activities
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Load roles relationship to ensure permissions can be checked
        $user->load('roles.permissions');
        
        // Check if user has permission to view all activity logs
        $canViewAll = $user->hasPermission('view-activity-logs') || 
                      $user->hasRole('super-admin') || 
                      $user->hasRole('Super Admin');

        $query = ActivityLog::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Permission-based filtering: Regular users only see their own activities
        if (!$canViewAll) {
            $query->where(function($q) use ($user) {
                $q->where('causer_type', \App\Models\User::class)
                  ->where('causer_id', $user->id);
            });
        }

        // Filter by user if specified (only if user has permission) - validate user_id to prevent SQL injection
        if ($request->filled('user_id') && $canViewAll) {
            $userId = filter_var($request->user_id, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1]
            ]);
            if ($userId !== false) {
                $query->where('causer_id', $userId)
                      ->where('causer_type', \App\Models\User::class);
            }
        }

        // Filter by event type if specified - sanitize to prevent SQL injection
        if ($request->filled('event')) {
            $event = preg_replace('/[^a-z0-9_\-]/', '', strtolower(trim($request->event)));
            if (!empty($event) && strlen($event) <= 50) {
                $query->where('event', $event);
            }
        }

        // Filter by date range if specified
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by action/description search - sanitize input to prevent SQL injection
        if ($request->filled('search')) {
            $search = $this->sanitizeSearchInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%");
            });
        }

        // Don't cache paginated results - always show fresh data
        $logs = $query->paginate(50);

        // Get filter options (only show all users if user has permission)
        if ($canViewAll) {
            $users = \App\Models\User::select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        } else {
            $users = collect([$user]);
        }

        // Get distinct events
        $eventsQuery = ActivityLog::select('event')->distinct();
        
        // Regular users only see events from their own activities
        if (!$canViewAll) {
            $eventsQuery->where('causer_type', \App\Models\User::class)
                  ->where('causer_id', $user->id);
        }
        
        $events = $eventsQuery->orderBy('event')->pluck('event');

        return view('admin.activity-logs.index', compact('logs', 'users', 'events', 'canViewAll'));
    }

    /**
     * Display the specified activity log
     */
    public function show(Request $request, ActivityLog $activityLog)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Load roles relationship
        $user->load('roles.permissions');
        
        // Check if user has permission to view all activity logs
        $canViewAll = $user->hasPermission('view-activity-logs') || 
                      $user->hasRole('super-admin') || 
                      $user->hasRole('Super Admin');
        
        // If user doesn't have permission, only show their own activities
        if (!$canViewAll) {
            if ($activityLog->causer_type !== \App\Models\User::class || 
                $activityLog->causer_id !== $user->id) {
                abort(403, 'You do not have permission to view this activity log.');
            }
        }
        
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

    /**
     * Sanitize search input to prevent SQL injection
     */
    protected function sanitizeSearchInput(string $input): string
    {
        // Remove SQL injection patterns
        $input = trim($input);
        
        // Remove SQL wildcards that could be used for injection
        $input = str_replace(['%', '_'], '', $input);
        
        // Remove potentially dangerous characters
        $input = preg_replace('/[^\w\s\-@\.]/', '', $input);
        
        // Limit length to prevent DoS
        $input = substr($input, 0, 100);
        
        return $input;
    }
}
