<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Add a business to the user's favorites.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToFavorites(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);

        $user = Auth::user();
        $businessId = $request->business_id;

        // Check if the business is already in favorites
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('business_id', $businessId)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business already in favorites'
            ], 409);
        }

        // Add to favorites
        $favorite = Favorite::create([
            'user_id' => $user->id,
            'business_id' => $businessId
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Business added to favorites',
            'data' => $favorite
        ], 201);
    }

    /**
     * Remove a business from the user's favorites.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromFavorites(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id'
        ]);

        $user = Auth::user();
        $businessId = $request->business_id;

        // Find and delete the favorite
        $favorite = Favorite::where('user_id', $user->id)
            ->where('business_id', $businessId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'status' => 'error',
                'message' => 'Business not found in favorites'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Business removed from favorites'
        ]);
    }

    /**
     * Get all favorites for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserFavorites(Request $request)
    {
        $user = Auth::user();
        $favorites = $user->favoritedBusinesses()
            ->with(['user:id,name,email'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $favorites
        ]);
    }

    /**
     * Check if a business is in the user's favorites.
     *
     * @param  int  $businessId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkFavorite($businessId)
    {
        $user = Auth::user();
        
        $isFavorite = Favorite::where('user_id', $user->id)
            ->where('business_id', $businessId)
            ->exists();

        return response()->json([
            'status' => 'success',
            'is_favorite' => $isFavorite
        ]);
    }

    /**
     * Get the count of favorites for a specific business.
     *
     * @param  int  $businessId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFavoritesCount($businessId)
    {
        $count = Favorite::where('business_id', $businessId)->count();
        
        return response()->json([
            'status' => 'success',
            'count' => $count
        ]);
    }
}
