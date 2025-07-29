<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'category_id',
        'name',
        'description',
        'product_image',
        'price',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['product_image_url'];

    // Accessor to get full URL of the image
    public function getProductImageUrlAttribute()
    {
        return $this->product_image 
            ? asset('storage/' . $this->product_image) 
            : null;
    }

    public function favourites()
{
    return $this->hasMany(Favourite::class);
}


public function productItems()
{
    return $this->hasMany(ProductItem::class);
}


}
