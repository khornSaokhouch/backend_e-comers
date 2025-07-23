<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    // Optional: If you want to explicitly define the table name
    // protected $table = 'shopping_carts';

    /**
     * The user that owns the shopping cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The items in the shopping cart.
     */
    public function items()
    {
        return $this->hasMany(ShoppingCartItem::class, 'cart_id');
    }
}
