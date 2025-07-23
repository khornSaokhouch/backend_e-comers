<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use Tymon\JWTAuth\Contracts\JWTSubject;  // Import the interface

class User extends Authenticatable implements JWTSubject , MustVerifyEmail  // Implement the interface here
{
    use HasFactory, Notifiable;  // Remove HasApiTokens if you only want JWT

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

     // ✅ Append this attribute to JSON output
     protected $appends = ['profile_image_url'];

     // ✅ This is the accessor
     public function getProfileImageUrlAttribute()
     {
         return $this->profile_image 
             ? asset('storage/' . $this->profile_image)
             : null;
     }

     public function isAdmin()
     {
         // Example: check if 'role' column equals 'admin'
         return $this->role === 'admin';
     }
     
    // Implement JWTSubject methods:
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

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
