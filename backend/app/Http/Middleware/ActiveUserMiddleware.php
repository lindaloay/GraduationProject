<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the authenticated user is active
        if ($request->user() && !$request->user()->is_active) {
            // If the request wants JSON, return a JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'تم تعطيل حسابك. يرجى التواصل مع الدعم الفني للمساعدة.',
                    'status' => 'inactive_account'
                ], 403);
            }
            
            // For non-API routes (if any), redirect to login
            auth()->logout();
            return redirect()->route('login')->with('error', 'تم تعطيل حسابك. يرجى التواصل مع الدعم الفني للمساعدة.');
        }
        
        return $next($request);
    }
} 