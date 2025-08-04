<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'role',
        'google_id',
        'avatar',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['profile_image_url'];

    // ✅ Accessor for profile_image_url
    public function getProfileImageUrlAttribute()
    {
        if (!$this->profile_image) {
            return null;
        }

        $disk = App::environment('local') ? 'public' : 'b2';

        return $disk === 'public'
            ? asset('storage/' . $this->profile_image)
            : Storage::disk('b2')->temporaryUrl($this->profile_image, now()->addMinutes(60));
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships
    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function shoppingCarts()
    {
        return $this->hasMany(ShoppingCart::class);
    }
}
