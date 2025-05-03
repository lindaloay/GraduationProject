<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Business;
use App\Models\RatingAspect;
use App\Models\FeedbackAspectRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'comment' => 'nullable|string|max:500',
            'aspect_ratings' => 'required|array',
            'aspect_ratings.*.aspect_id' => 'required',
            'aspect_ratings.*.rating' => 'required|integer|min:1|max:5',
            'aspect_ratings.*.name' => 'nullable|string|max:100',
            'aspect_ratings.*.is_custom' => 'nullable|boolean'
        ]);

        // Check if user has already submitted feedback for this business
        $existingFeedback = Feedback::where('user_id', Auth::id())
            ->where('business_id', $request->business_id)
            ->first();

        if ($existingFeedback) {
            return response()->json([
                'status' => 'error',
                'message' => 'لقد قمت بتقييم هذا المكان مسبقاً'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Calculate overall rating as the average of aspect ratings
            $overallRating = 0;
            $aspectRatingsCount = count($request->aspect_ratings);
            
            if ($aspectRatingsCount > 0) {
                $totalRating = 0;
                foreach ($request->aspect_ratings as $aspectRating) {
                    $totalRating += $aspectRating['rating'];
                }
                $overallRating = round($totalRating / $aspectRatingsCount, 1);
            }
            
            // Create the main feedback with calculated overall rating
            $feedback = Feedback::create([
                'user_id' => Auth::id(),
                'business_id' => $request->business_id,
                'rating' => $overallRating,
                'comment' => $request->comment
            ]);
            
            // Save aspect ratings
            foreach ($request->aspect_ratings as $aspectRating) {
                $aspectId = $aspectRating['aspect_id'];
                
                // Check if this is a custom aspect
                if (isset($aspectRating['is_custom']) && $aspectRating['is_custom'] && isset($aspectRating['name'])) {
                    // Create a new rating aspect
                    $business = Business::find($request->business_id);
                    $newAspect = RatingAspect::create([
                        'name' => $aspectRating['name'],
                        'description' => 'تقييم مضاف بواسطة المستخدم',
                        'category_id' => $business->category_id,
                        'is_user_created' => true
                    ]);
                    $aspectId = $newAspect->id;
                }
                
                FeedbackAspectRating::create([
                    'feedback_id' => $feedback->id,
                    'aspect_id' => $aspectId,
                    'rating' => (int)$aspectRating['rating']
                ]);
            }
            
            // Update business average rating
            $business = Business::find($request->business_id);
            $business->updateRating();
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال تقييمك بنجاح',
                'feedback' => $feedback
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء حفظ التقييم: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBusinessFeedbacks($businessId)
    {
        try {
            $feedbacks = Feedback::where('business_id', $businessId)
                ->with([
                    'user:id,name',
                    'aspectRatings.aspect:id,name,description'
                ])
                ->select('id', 'user_id', 'rating', 'comment', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'feedbacks' => $feedbacks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching feedbacks: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getBusinessRatingAspects($businessId)
    {
        try {
            // Get the business category
            $business = Business::findOrFail($businessId);
            
            // Get rating aspects for this business category
            $ratingAspects = RatingAspect::where('category_id', $business->category_id)
                ->select('id', 'name', 'description', 'is_user_created')
                ->get();
                
            if ($ratingAspects->isEmpty()) {
                // If no specific aspects found, return default aspect (Quality of Service)
                $ratingAspects = [
                    [
                        'id' => 0, // Use a placeholder ID
                        'name' => 'جودة الخدمة',
                        'description' => 'تقييم مستوى جودة الخدمة المقدمة',
                        'is_user_created' => false
                    ]
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'rating_aspects' => $ratingAspects
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching rating aspects: ' . $e->getMessage()
            ], 500);
        }
    }
} 