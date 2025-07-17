<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'company_name',
        'email',
        'country_region',
        'street_address',
        'phone_number',
        'status',  // add this if you want to mass assign it
    ];
}
