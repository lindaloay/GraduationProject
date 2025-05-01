<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        try {
            // Get query parameters for pagination and filtering
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $search = $request->input('search', '');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $filterRole = $request->input('role', ''); // 'admin', 'business', 'user'
            
            // Build query
            $query = User::query()
                ->select(['id', 'name', 'email', 'phone', 'address', 'city', 'country', 'is_business_owner', 'is_admin', 'is_active', 'created_at', 'updated_at']);
                
            // Apply search if provided
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('country', 'like', "%{$search}%");
                });
            }
            
            // Apply role filter if provided
            if ($filterRole === 'admin') {
                $query->where('is_admin', 1);
            } elseif ($filterRole === 'business') {
                $query->where('is_business_owner', 1)->where('is_admin', 0);
            } elseif ($filterRole === 'user') {
                $query->where('is_business_owner', 0)->where('is_admin', 0);
            }
            
            // Apply sorting
            if ($sortBy === 'role') {
                // For role sorting, we need to use a raw SQL expression to sort by the user's role type
                // Order: admin first, then business owners, then regular users
                if ($sortOrder === 'asc') {
                    $query->orderByRaw('CASE WHEN is_admin = 1 THEN 1 WHEN is_business_owner = 1 THEN 2 ELSE 3 END ASC');
                } else {
                    $query->orderByRaw('CASE WHEN is_admin = 1 THEN 1 WHEN is_business_owner = 1 THEN 2 ELSE 3 END DESC');
                }
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            // Get paginated results
            $users = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Format the data
            $formattedUsers = $users->getCollection()->map(function ($user) {
                // Determine the user's role
                $role = $user->is_admin ? 'admin' : ($user->is_business_owner ? 'business' : 'user');
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'is_business_owner' => (bool) $user->is_business_owner,
                    'is_admin' => (bool) $user->is_admin,
                    'is_active' => (bool) $user->is_active,
                    'role' => $role,
                    'created_at' => $user->created_at->toISOString(),
                    'created_since' => $user->created_at->diffForHumans(),
                    'updated_at' => $user->updated_at->toISOString(),
                ];
            });
            
            // Create a custom paginated response
            $response = [
                'status' => 'success',
                'data' => $formattedUsers,
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
                'timestamp' => Carbon::now()->toISOString()
            ];
            
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve users: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Display the specified user details
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Get user's businesses if they are a business owner
            $businesses = [];
            if ($user->is_business_owner) {
                $businesses = $user->businesses()
                    ->select(['id', 'name', 'email', 'phone', 'category_id', 'created_at'])
                    ->with('category:id,name,name_ar')
                    ->get()
                    ->map(function ($business) {
                        return [
                            'id' => $business->id,
                            'name' => $business->name,
                            'email' => $business->email,
                            'phone' => $business->phone,
                            'category' => $business->category ? [
                                'id' => $business->category->id,
                                'name' => $business->category->name,
                                'name_ar' => $business->category->name_ar,
                            ] : null,
                            'created_at' => $business->created_at->toISOString(),
                            'created_since' => $business->created_at->diffForHumans(),
                        ];
                    });
            }
            
            // Format the user data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'country' => $user->country,
                'is_business_owner' => (bool) $user->is_business_owner,
                'is_admin' => (bool) $user->is_admin,
                'is_active' => (bool) $user->is_active,
                'created_at' => $user->created_at->toISOString(),
                'created_since' => $user->created_at->diffForHumans(),
                'updated_at' => $user->updated_at->toISOString(),
                'businesses' => $businesses,
            ];
            
            return response()->json([
                'status' => 'success',
                'user' => $userData,
                'timestamp' => Carbon::now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found or error retrieving user details: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update user's basic details
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
            ]);

            $user = User::findOrFail($id);
            
            // Prevent changing admin status through this method
            // Only admin role field is protected, all other fields can be updated
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->city = $request->city;
            $user->country = $request->country;
            $user->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'User details updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'is_business_owner' => (bool) $user->is_business_owner,
                    'is_admin' => (bool) $user->is_admin,
                    'is_active' => (bool) $user->is_active,
                ],
                'timestamp' => Carbon::now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Don't allow deactivating admin users
            if ($user->is_admin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Admin users cannot be deactivated',
                ], 403);
            }
            
            // Toggle the active status
            $user->is_active = !$user->is_active;
            $user->save();
            
            $statusMessage = $user->is_active ? 'تم تفعيل المستخدم بنجاح' : 'تم تعطيل المستخدم بنجاح';
            
            return response()->json([
                'status' => 'success',
                'message' => $statusMessage,
                'user' => [
                    'id' => $user->id,
                    'is_active' => (bool) $user->is_active,
                ],
                'timestamp' => Carbon::now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to toggle user status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Generate a random password
            $newPassword = Str::random(10);
            
            // Update user with new password
            $user->password = Hash::make($newPassword);
            $user->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'new_password' => $newPassword, // This should be sent via email in a production environment
                'timestamp' => Carbon::now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reset password: ' . $e->getMessage(),
            ], 500);
        }
    }
} 