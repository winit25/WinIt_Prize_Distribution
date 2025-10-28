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
        
        // Check if user has the specific permission
        if (!$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }
        
        return $next($request);
    }
}
