<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    /**
     * Display a listing of businesses with search, filter, and pagination
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Business::with(['user', 'mainPicture', 'category']);
        
        // Apply search filters
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
        
        // Apply category filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Apply rating filter
        if ($request->has('min_rating') && is_numeric($request->min_rating)) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        // Sort results
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_order', 'desc');
        
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['name', 'rating', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Paginate results
        $perPage = $request->input('per_page', 10);
        $businesses = $query->paginate($perPage);
        
        // Format business data with time since
        $formattedBusinesses = $businesses->map(function ($business) {
            // Add created_since field
            $business->created_since = $this->getTimeSince($business->created_at);
            
            // Format main picture URL
            if ($business->mainPicture) {
                $business->main_picture_url = $business->mainPicture->url;
            }
            
            return $business;
        });
        
        return response()->json([
            'status' => 'success',
            'businesses' => $formattedBusinesses,
            'pagination' => [
                'total' => $businesses->total(),
                'per_page' => $businesses->perPage(),
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage(),
                'from' => $businesses->firstItem(),
                'to' => $businesses->lastItem()
            ]
        ]);
    }
    
    /**
     * Get all categories for filtering
     * 
     * @return \Illuminate\Http\Response
     */
    public function getCategories()
    {
        $categories = Category::where('is_active', true)->get();
        
        return response()->json([
            'status' => 'success',
            'categories' => $categories
        ]);
    }
    
    /**
     * Display the specified business with details
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $business = Business::with(['user', 'mainPicture', 'galleryPictures', 'category', 'feedbacks.user'])
            ->findOrFail($id);
            
        // Add created_since field
        $business->created_since = $this->getTimeSince($business->created_at);
        
        // Add direct access to picture URLs
        if ($business->mainPicture) {
            $business->main_picture_url = $business->mainPicture->image_url;
        }
        
        // Add gallery picture URLs
        $business->gallery_pictures_urls = $business->galleryPictures->pluck('image_url')->toArray();
        
        // Format feedback dates
        if ($business->feedbacks) {
            foreach ($business->feedbacks as $feedback) {
                $feedback->created_since = $this->getTimeSince($feedback->created_at);
            }
        }
        
        return response()->json([
            'status' => 'success',
            'business' => $business
        ]);
    }
    
    /**
     * Get time since creation in Arabic format
     *
     * @param  \Carbon\Carbon|string  $date
     * @return string
     */
    private function getTimeSince($date)
    {
        if (!$date) return '';
        
        $carbon = $date instanceof Carbon ? $date : new Carbon($date);
        $now = Carbon::now();
        $diff = $carbon->diffInSeconds($now);
        
        if ($diff < 60) {
            return 'منذ لحظات';
        } else if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "منذ {$minutes} " . ($minutes == 1 ? 'دقيقة' : 'دقائق');
        } else if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "منذ {$hours} " . ($hours == 1 ? 'ساعة' : 'ساعات');
        } else if ($diff < 2592000) {
            $days = floor($diff / 86400);
            return "منذ {$days} " . ($days == 1 ? 'يوم' : 'أيام');
        } else if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "منذ {$months} " . ($months == 1 ? 'شهر' : 'أشهر');
        } else {
            $years = floor($diff / 31536000);
            return "منذ {$years} " . ($years == 1 ? 'سنة' : 'سنوات');
        }
    }
}
