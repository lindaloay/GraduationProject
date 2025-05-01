<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAuthenticated;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(EnsureAuthenticated::class);
    }

    /**
     * Get the authenticated user's profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Initialize the response data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_business_owner' => $user->is_business_owner,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
            ];

            // Only load business data if the user is a business owner
            if ($user->is_business_owner) {
                $user->load('business');
                $userData['business'] = $user->business ? [
                    'id' => $user->business->id,
                    'name' => $user->business->name,
                    'category' => $user->business->category,
                    'status' => $user->business->status
                ] : null;
            }

            return response()->json([
                'success' => true,
                'user' => $userData
            ]);
        } catch (\Exception $e) {
            \Log::error('Profile Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();
            
            // Base validation rules for all users
            $baseRules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ];

            // Additional rules for business owners
            $businessOwnerRules = [
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'country' => 'required|string|max:100',
            ];

            // Combine rules based on user type
            $validationRules = $user->is_business_owner 
                ? array_merge($baseRules, $businessOwnerRules)
                : array_merge($baseRules, [
                    'phone' => 'nullable|string|max:20',
                    'address' => 'nullable|string|max:255',
                    'city' => 'nullable|string|max:100',
                    'country' => 'nullable|string|max:100',
                ]);

            // Validate the request data
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update user profile
            $user->update($request->only([
                'name',
                'email',
                'phone',
                'address',
                'city',
                'country'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 