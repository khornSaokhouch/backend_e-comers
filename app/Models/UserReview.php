<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReview extends Model
{
    use HasFactory;

    // ✅ Use plural table name if your table is named `user_reviews`
    protected $table = 'user_review';

    protected $fillable = [
        'user_id',
        'order_product_id',
        'review_text',
        'rating',
    ];

    // ✅ Define relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    
    
}
