<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model

{

    protected $table = 'order_lines';
    
    protected $fillable = [
        'order_id',
        'product_item_id',
        'quantity',
        'price',
    ];

    // Define relations, if any
    public function order()
    {
        return $this->belongsTo(ShopOrder::class);
    }

    public function productItem()
    {
        return $this->belongsTo(ProductItem::class, 'product_item_id')->with('product');
    }
    
}
