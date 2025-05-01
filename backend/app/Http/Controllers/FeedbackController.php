<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
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

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'business_id' => $request->business_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        // Update business average rating
        $business = Business::find($request->business_id);
        $business->updateRating();

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال تقييمك بنجاح',
            'feedback' => $feedback
        ]);
    }

    public function getBusinessFeedbacks($businessId)
    {
        try {
            $feedbacks = Feedback::where('business_id', $businessId)
                ->with('user:id,name')
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
} 