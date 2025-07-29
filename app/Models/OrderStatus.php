<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $table = 'order_statuses'; // not 'order_status'


    protected $fillable = [
        'status',
    ];

    public function shopOrders()
    {
        return $this->hasMany(ShopOrder::class, 'order_status_id');
    }
}
