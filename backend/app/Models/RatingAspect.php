<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingAspect extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id'
    ];

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'category_id');
    }

    public function ratings()
    {
        return $this->hasMany(BusinessRating::class, 'aspect_id');
    }
} 