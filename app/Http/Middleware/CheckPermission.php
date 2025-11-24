<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Super Admin has access to everything
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }
        
        // HARD RESTRICTION: Audit role cannot perform write operations
        // This is a critical security requirement - Audit is read-only
        if ($user->hasRole('audit')) {
            $writePermissions = [
                'upload-csv',
                'process-batches',
                'manage-users',
                'clear-activity-logs',
                'manage-notifications'
            ];
            
            if (in_array($permission, $writePermissions)) {
                abort(403, 'Audit role is restricted to read-only access. Write operations are not permitted.');
            }
        }
        
        // Check if user has the specific permission
        if (!$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }
        
        return $next($request);
    }
}
