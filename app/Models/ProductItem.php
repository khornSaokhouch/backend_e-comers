<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity_in_stock',
    ];

    /**
     * The parent product this item belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Shopping cart items that reference this product item.
     */
    public function shoppingCartItems()
    {
        return $this->hasMany(ShoppingCartItem::class, 'product_item_id');
    }
}
