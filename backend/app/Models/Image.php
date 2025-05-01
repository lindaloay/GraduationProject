<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'url',
        'file_name',
        'file_path',
        'type',
        'order'
    ];

    /**
     * Get the business that owns the image.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the full URL for the image.
     */
    public function getImageUrlAttribute()
    {
        // We primarily use file_path
        if ($this->file_path) {
            // If file_path already starts with /storage
            if (strpos($this->file_path, '/storage/') === 0) {
                return url('') . $this->file_path;
            }
            
            // Otherwise treat it as a relative path
            return url('/storage/' . $this->file_path);
        }
        
        // For backward compatibility, check url if it exists
        if (isset($this->attributes['url']) && $this->attributes['url']) {
            // If url is already a full URL, return it
            if (strpos($this->attributes['url'], 'http') === 0) {
                return $this->attributes['url'];
            }
            
            // If it starts with /storage, append the base URL
            if (strpos($this->attributes['url'], '/storage/') === 0) {
                return url('') . $this->attributes['url'];
            }
            
            // Otherwise treat as a relative path
            return url('/storage/' . $this->attributes['url']);
        }
        
        return null;
    }
}
