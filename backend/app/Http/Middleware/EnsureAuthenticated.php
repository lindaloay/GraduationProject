<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!Auth::check()) {
                \Log::info('Unauthorized access attempt');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Authentication token required'
                ], 401);
            }

            return $next($request);
        } catch (\Exception $e) {
            \Log::error('Auth Middleware Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Authentication error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
 