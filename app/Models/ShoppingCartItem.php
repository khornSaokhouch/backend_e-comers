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

    /**
     * The cart this item belongs to.
     */
    public function cart()
    {
        return $this->belongsTo(ShoppingCart::class, 'cart_id');
    }

    /**
     * The product item this cart item refers to.
     */
    public function productItem()
    {
        return $this->belongsTo(ProductItem::class, 'product_item_id');
    }
}
