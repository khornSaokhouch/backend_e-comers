<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    // ✅ Set the correct table name
    protected $table = 'shipping_method';

    protected $fillable = ['name', 'price'];

    public function shopOrders()
    {
        return $this->hasMany(ShopOrder::class, 'shipping_method_id');
    }
}
