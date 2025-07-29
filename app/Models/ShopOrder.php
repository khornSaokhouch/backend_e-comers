<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopOrder extends Model
{
    use HasFactory;

    protected $table = 'shop_order';

    protected $fillable = [
        'user_id',
        'order_date',
        'payment_method_id',
        'shipping_address',
        'shipping_method_id',
        'order_total',
        'order_status_id',
    ];

    // Cast order_date to a datetime object, ensuring proper format on save
    protected $casts = [
        'order_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(UserPaymentMethod::class, 'payment_method_id');
    }
    
    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }
    

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }

    public function orderHistories()
    {
        return $this->hasMany(OrderHistory::class, 'order_id');
    }

    public function orderLines()
    {
        return $this->hasMany(OrderLine::class, 'order_id');
    }
    
}
