<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Basic validation for all users
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_business_owner' => 'required|boolean',
        ];
        
        // Add additional validation rules for business owners
        if ($request->is_business_owner) {
            $rules = array_merge($rules, [
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create user data array with required fields
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_business_owner' => $request->is_business_owner,
            'is_admin' => false,
        ];
        
        // Add additional fields only for business owners
        if ($request->is_business_owner) {
            $userData = array_merge($userData, [
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
            ]);
        }

        $user = User::create($userData);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('auth.register_successful'),
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login user and create token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => __('auth.invalid_credentials')
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        
        // Check if user account is active
        if (!$user->is_active) {
            // Logout the user
            Auth::logout();
            
            return response()->json([
                'message' => 'تم تعطيل حسابك. يرجى التواصل مع الدعم الفني للمساعدة.',
                'status' => 'inactive_account'
            ], 400);
        }
        
        // Check if user is an admin
        if ($user->is_admin) {
            // Logout the user
            Auth::logout();
            
            return response()->json([
                'message' => 'Admin users must use the admin login page',
                'redirectTo' => '/admin/login'
            ], 403);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => __('auth.login_successful'),
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout user (revoke the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('auth.logout_successful')
        ]);
    }
} 