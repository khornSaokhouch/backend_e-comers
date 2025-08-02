<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReview extends Model
{
    protected $table = 'user_review'; // if your table name is singular

    protected $fillable = [
        'user_id',
        'order_product_id',
        'review_text',
        'rating',
    ];
}
