<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Business extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'website',
        'phone',
        'working_times',
        'category_id',
        'description',
        'facebook_link',
        'instagram_link',
        'twitter_link',
        'has_wifi',
        'has_online_booking',
        'near_transportation',
        'has_parking',
        'latitude',
        'longitude',
        'status',
        'rating',
        'rating_count'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_wifi' => 'boolean',
        'has_online_booking' => 'boolean',
        'near_transportation' => 'boolean',
        'has_parking' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'float',
        'rating_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the business.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all images for this business.
     */
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get the main picture for this business.
     */
    public function mainPicture()
    {
        return $this->hasOne(Image::class)->where('type', 'main');
    }

    /**
     * Get the gallery pictures for this business.
     */
    public function galleryPictures()
    {
        return $this->hasMany(Image::class)->where('type', 'gallery')->orderBy('order');
    }

    /**
     * Get the main picture URL.
     */
    public function getMainPictureUrlAttribute()
    {
        $mainImage = $this->mainPicture;
        if ($mainImage) {
            return $mainImage->image_url;
        }
        
        return null;
    }

    /**
     * Get the gallery picture URLs.
     */
    public function getGalleryPicturesUrlsAttribute()
    {
        $galleryImages = $this->galleryPictures;
        if ($galleryImages->count() > 0) {
            return $galleryImages->pluck('image_url')->toArray();
        }
        
        return [];
    }

    public function updateRating()
    {
        $averageRating = $this->feedbacks()->avg('rating');
        $this->rating = round($averageRating, 1);
        $this->rating_count = $this->feedbacks()->count();
        $this->save();
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Get the users who have favorited this business.
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'business_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Get the favorites for this business.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the category that the business belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
} 