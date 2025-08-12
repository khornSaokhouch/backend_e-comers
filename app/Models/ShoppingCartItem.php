<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_item_id',
        'qty',
    ];

    public function cart()
    {
        return $this->belongsTo(ShoppingCart::class, 'cart_id');
    }

    public function productItem()
    {
        return $this->belongsTo(ProductItem::class);
    }
    

    
}
