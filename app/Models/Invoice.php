<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';

    protected $fillable = [
        'order_id',
        'invoice_number',
        'generated_at',
        'total_amount',
    ];

    protected $dates = [
        'generated_at',
    ];

    public function order()
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }
}
