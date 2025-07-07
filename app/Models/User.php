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
    ];
    

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin()
    {
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
}
