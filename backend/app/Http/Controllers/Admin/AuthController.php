<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            // Check if user account is active
            if (!$user->is_active) {
                // Logout the user
                Auth::logout();
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'تم تعطيل حسابك. يرجى التواصل مع الدعم الفني للمساعدة.'
                ], 403);
            }
            
            if ($user->is_admin) {
                // Generate token for admin
                $token = $user->createToken('admin-token', ['admin'])->plainTextToken;
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Admin login successful',
                    'user' => $user,
                    'token' => $token
                ]);
            }
            
            // If user is not an admin, logout
            Auth::logout();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Admin access required.'
            ], 400);
        }
        
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Admin logged out successfully'
        ]);
    }

    /**
     * Get admin profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()
        ]);
    }
}
