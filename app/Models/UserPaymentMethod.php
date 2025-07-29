<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'user_payment_method';

    protected $fillable = [
        'user_id',
        'payment_type_id',
        'provider',
        'card_number',
        'expiry_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function shopOrders()
    {
        return $this->hasMany(ShopOrder::class, 'payment_method_id');
    }
}
