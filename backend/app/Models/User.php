<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_business_owner',
        'is_admin',
        'is_active',
        'phone',
        'address',
        'city',
        'country',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the business associated with the user.
     * @deprecated Use businesses() instead for users who may have multiple businesses
     */
    public function business()
    {
        return $this->hasOne(Business::class);
    }
    
    /**
     * Get all businesses owned by the user.
     * This allows a user to have multiple businesses.
     */
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    /**
     * Get the user's favorite businesses.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the businesses that the user has favorited.
     */
    public function favoritedBusinesses()
    {
        return $this->belongsToMany(Business::class, 'favorites', 'user_id', 'business_id')
                    ->withTimestamps();
    }
}
