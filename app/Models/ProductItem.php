<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Company;

class ProductItem extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_in_stock',
        'company_id', // Add this if it's fillable
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    

    public function shoppingCartItems()
    {
        return $this->hasMany(ShoppingCartItem::class, 'product_item_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
