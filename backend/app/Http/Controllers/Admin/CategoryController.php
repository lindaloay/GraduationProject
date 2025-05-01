<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        
        return response()->json([
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $category = Category::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'description' => $request->description,
            'icon' => $request->icon,
            'is_active' => $request->input('is_active', true)
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'category' => $category
        ]);
    }

    /**
     * Update the specified category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'name_ar' => 'string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $category = Category::findOrFail($id);
        $category->update($request->only(['name', 'name_ar', 'description', 'icon', 'is_active']));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Check if there are businesses using this category
        $businessCount = Business::where('category_id', $id)->count();
        
        if ($businessCount > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete category because it is used by ' . $businessCount . ' businesses'
            ], 422);
        }
        
        $category->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ]);
    }
    
    /**
     * Get statistics about categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function stats()
    {
        $categories = Category::withCount('businesses')->get();
        $totalCategories = $categories->count();
        $activeCategories = $categories->where('is_active', true)->count();
        
        return response()->json([
            'status' => 'success',
            'stats' => [
                'total' => $totalCategories,
                'active' => $activeCategories,
                'inactive' => $totalCategories - $activeCategories,
                'categories' => $categories
            ]
        ]);
    }
} 