<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackAspectRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_id',
        'aspect_id',
        'rating'
    ];

    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }

    public function aspect()
    {
        return $this->belongsTo(RatingAspect::class, 'aspect_id');
    }
} 