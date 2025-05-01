<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedbacks with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Feedback::with(['user:id,name,email', 'business:id,name,category_id', 'business.category:id,name,name_ar']);
            
            // Apply search filter if provided
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('business', function ($businessQuery) use ($search) {
                          $businessQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }
            
            // Filter by business ID if provided
            if ($request->has('business_id') && !empty($request->input('business_id'))) {
                $query->where('business_id', $request->input('business_id'));
            }
            
            // Filter by user ID if provided
            if ($request->has('user_id') && !empty($request->input('user_id'))) {
                $query->where('user_id', $request->input('user_id'));
            }
            
            // Filter by rating if provided
            if ($request->has('rating') && !empty($request->input('rating'))) {
                $query->where('rating', $request->input('rating'));
            }
            
            // Set up sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            // Handle special sorting cases
            if ($sortBy === 'business') {
                $query->join('businesses', 'feedbacks.business_id', '=', 'businesses.id')
                      ->orderBy('businesses.name', $sortOrder)
                      ->select('feedbacks.*'); // Ensure we only select fields from the feedbacks table
            } elseif ($sortBy === 'user') {
                $query->join('users', 'feedbacks.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortOrder)
                      ->select('feedbacks.*'); // Ensure we only select fields from the feedbacks table
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
            
            // Paginate results
            $perPage = $request->input('per_page', 10);
            $feedbacks = $query->paginate($perPage);
            
            // Add time since for each feedback
            $feedbacks->getCollection()->transform(function ($feedback) {
                $feedback->created_since = $this->getTimeSince($feedback->created_at);
                return $feedback;
            });
            
            return response()->json([
                'status' => 'success',
                'feedbacks' => $feedbacks->items(),
                'pagination' => [
                    'total' => $feedbacks->total(),
                    'per_page' => $feedbacks->perPage(),
                    'current_page' => $feedbacks->currentPage(),
                    'last_page' => $feedbacks->lastPage(),
                    'from' => $feedbacks->firstItem(),
                    'to' => $feedbacks->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching feedbacks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified feedback.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $feedback = Feedback::with([
                'user:id,name,email,phone,is_business_owner,is_admin',
                'business',
                'business.category'
            ])->findOrFail($id);
            
            $feedback->created_since = $this->getTimeSince($feedback->created_at);
            
            return response()->json([
                'status' => 'success',
                'feedback' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback not found'
            ], 404);
        }
    }

    /**
     * Remove the specified feedback.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            $businessId = $feedback->business_id;
            
            // Delete the feedback
            $feedback->delete();
            
            // Update the business average rating
            $business = $feedback->business;
            if ($business) {
                $business->updateRating();
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Feedback deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting feedback: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get statistics about feedbacks.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        try {
            $totalFeedbacks = Feedback::count();
            $avgRating = Feedback::avg('rating');
            
            // Get distribution of ratings (1-5 stars)
            $ratingDistribution = [];
            for ($i = 1; $i <= 5; $i++) {
                $count = Feedback::where('rating', $i)->count();
                $percentage = $totalFeedbacks > 0 ? round(($count / $totalFeedbacks) * 100, 1) : 0;
                $ratingDistribution[$i] = [
                    'count' => $count,
                    'percentage' => $percentage
                ];
            }
            
            // Get feedback counts by month (last 6 months)
            $feedbackTrend = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $year = $date->year;
                $month = $date->month;
                
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                
                $count = Feedback::whereBetween('created_at', [$startDate, $endDate])->count();
                
                $feedbackTrend[] = [
                    'month' => $date->format('M Y'),
                    'count' => $count
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'stats' => [
                    'total' => $totalFeedbacks,
                    'average_rating' => round($avgRating, 1),
                    'rating_distribution' => $ratingDistribution,
                    'trend' => $feedbackTrend
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching feedback statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculate time since for date formatting
     *
     * @param \Carbon\Carbon|string $date
     * @return string
     */
    private function getTimeSince($date)
    {
        if (!$date) return '';
        
        $date = $date instanceof Carbon ? $date : new Carbon($date);
        $now = Carbon::now();
        $diff = $date->diffInSeconds($now);
        
        if ($diff < 60) {
            return 'منذ لحظات';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "منذ {$minutes} " . ($minutes == 1 ? 'دقيقة' : 'دقائق');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "منذ {$hours} " . ($hours == 1 ? 'ساعة' : 'ساعات');
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return "منذ {$days} " . ($days == 1 ? 'يوم' : 'أيام');
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "منذ {$months} " . ($months == 1 ? 'شهر' : 'أشهر');
        } else {
            $years = floor($diff / 31536000);
            return "منذ {$years} " . ($years == 1 ? 'سنة' : 'سنوات');
        }
    }
} 