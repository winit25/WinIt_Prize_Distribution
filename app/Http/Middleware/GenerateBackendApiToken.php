<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GenerateBackendApiToken
{
    /**
     * Handle an incoming request.
     *
     * Generates and stores a Sanctum API token for the authenticated user
     * to use when making requests to the backend API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only generate token if user is authenticated and doesn't have one in session
        if (Auth::check() && !session()->has('backend_api_token')) {
            $user = Auth::user();
            
            // Delete any existing backend API tokens for this user
            $user->tokens()->where('name', 'backend-api-token')->delete();
            
            // Create new token
            $token = $user->createToken('backend-api-token');
            
            // Store token in session
            session(['backend_api_token' => $token->plainTextToken]);
        }

        return $next($request);
    }
}
