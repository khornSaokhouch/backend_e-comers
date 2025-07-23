<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'image',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optional: if you want to return full URL for images
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'category_id');
    }

    // Many-to-many relationship with Promotion
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_category', 'category_id', 'promotion_id')->withTimestamps();
    }
}
