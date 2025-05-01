<?php

namespace App\Http\Controllers;

use App\Models\BusinessCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessCategoryController extends Controller
{
    /**
     * Display a listing of business categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = BusinessCategory::query();
            
            // Only include active categories unless include_inactive=true is provided
            if (!$request->has('include_inactive') || !$request->input('include_inactive')) {
                $query->where('is_active', true);
            }
            
            // Get the categories and load the businesses count
            $categories = $query->withCount('businesses')->get();
            
            return response()->json([
                'status' => 'success',
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created business category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:business_categories',
            'name_ar' => 'required|string|max:255|unique:business_categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = BusinessCategory::create($request->all());
            
            // Load businesses count for consistency
            $category->businesses_count = 0;
            
            return response()->json([
                'status' => 'success',
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified business category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $category = BusinessCategory::withCount('businesses')->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }
    }

    /**
     * Update the specified business category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:business_categories,name,' . $id,
            'name_ar' => 'string|max:255|unique:business_categories,name_ar,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = BusinessCategory::findOrFail($id);
            $category->update($request->all());
            
            // Reload the category with businesses count
            $category = BusinessCategory::withCount('businesses')->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified business category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $category = BusinessCategory::withCount('businesses')->findOrFail($id);
            
            // Check if category has businesses
            if ($category->businesses_count > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete category with associated businesses',
                    'businesses_count' => $category->businesses_count
                ], 400);
            }
            
            $category->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get businesses by category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessesByCategory($id)
    {
        try {
            $category = BusinessCategory::withCount('businesses')->findOrFail($id);
            $businesses = $category->businesses()
                ->join('users', 'businesses.user_id', '=', 'users.id')
                ->select([
                    'businesses.id',
                    'businesses.name',
                    'businesses.email',
                    'businesses.phone',
                    'users.address',
                    'users.city',
                    'users.country',
                    'businesses.latitude',
                    'businesses.longitude',
                    'businesses.working_times',
                    'businesses.has_wifi',
                    'businesses.has_online_booking',
                    'businesses.near_transportation',
                    'businesses.has_parking',
                    'businesses.facebook_link',
                    'businesses.instagram_link',
                    'businesses.twitter_link',
                    'businesses.website',
                    'businesses.description',
                    'businesses.category_id',
                    'businesses.created_at',
                    'businesses.rating',
                    'businesses.rating_count'
                ])
                ->get();
            
            // Add image URLs to businesses
            foreach ($businesses as $business) {
                $business->main_picture_url = $business->mainPictureUrl;
                $business->gallery_picture_urls = $business->galleryPicturesUrls;
            }
            
            return response()->json([
                'status' => 'success',
                'category' => $category,
                'businesses' => $businesses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching businesses by category: ' . $e->getMessage()
            ], 500);
        }
    }
} 