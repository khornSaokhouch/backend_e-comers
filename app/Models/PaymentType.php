<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    protected $table = 'payment_type';

    protected $fillable = [
        'type', // or whatever columns you have
    ];

    public function userPaymentMethods()
    {
        return $this->hasMany(UserPaymentMethod::class, 'payment_type_id');
    }
}
