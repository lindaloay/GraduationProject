<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\User;
use App\Models\Feedback;
use App\Models\BusinessCategory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard with statistics
     */
    public function index()
    {
        // Get statistics for the dashboard
        $totalUsers = User::count();
        $totalBusinesses = Business::count();
        $totalFeedbacks = Feedback::count();
        $totalCategories = BusinessCategory::count();
        
        // Get recent businesses (last 7)
        $recentBusinesses = Business::orderBy('created_at', 'desc')
            ->with(['user:id,name,email', 'category:id,name,name_ar', 'mainPicture'])
            ->take(7)
            ->get();
            
        // Get recent users (last 7)
        $recentUsers = User::orderBy('created_at', 'desc')
            ->select(['id', 'name', 'email', 'is_business_owner', 'is_admin', 'created_at'])
            ->take(7)
            ->get();
            
        // Get recent feedbacks (last 7)
        $recentFeedbacks = Feedback::orderBy('created_at', 'desc')
            ->with(['user:id,name', 'business:id,name'])
            ->take(7)
            ->get();

        // Format the data for frontend
        $formattedBusinesses = $recentBusinesses->map(function ($business) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'main_picture_url' => $business->mainPictureUrl,
                'user' => $business->user ? [
                    'id' => $business->user->id,
                    'name' => $business->user->name,
                ] : null,
                'category' => $business->category ? [
                    'id' => $business->category->id,
                    'name' => $business->category->name,
                    'name_ar' => $business->category->name_ar,
                ] : null,
                'created_at' => $business->created_at->toISOString(),
                'created_since' => $business->created_at->diffForHumans(),
            ];
        });
            
        return response()->json([
            'status' => 'success',
            'statistics' => [
                'totalUsers' => $totalUsers,
                'totalBusinesses' => $totalBusinesses,
                'totalFeedbacks' => $totalFeedbacks,
                'totalCategories' => $totalCategories
            ],
            'recentData' => [
                'businesses' => $formattedBusinesses,
                'users' => $recentUsers,
                'feedbacks' => $recentFeedbacks
            ],
            'timestamp' => Carbon::now()->toISOString()
        ]);
    }
}
