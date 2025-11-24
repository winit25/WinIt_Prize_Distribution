<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if ($user && $user->must_change_password) {
            // Allow access to password change routes, logout, and API routes
            if ($request->routeIs('password.change') || 
                $request->routeIs('password.change.update') || 
                $request->routeIs('password.update') ||
                $request->routeIs('logout') ||
                $request->is('api/*')) {
                return $next($request);
            }
            
            // Redirect to password change page with warning
            return redirect()->route('password.change')
                ->with('warning', 'You must change your password before continuing.');
        }
        
        return $next($request);
    }
}
