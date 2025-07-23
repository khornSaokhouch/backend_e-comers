<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
        'start_date',
        'end_date',
    ];

    public function categories()
    {
        // Specify the pivot table and the foreign key names explicitly
        return $this->belongsToMany(Category::class, 'promotion_category', 'promotion_id', 'category_id')->withTimestamps();
    }
}
