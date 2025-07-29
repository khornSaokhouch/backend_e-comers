<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    // If your table name is not 'companies', specify it here:
    // protected $table = 'companies';

    // Specify the fillable fields to allow mass assignment
    protected $fillable = [
        'name',
        'telegram_chat_id',
        // add other fields here as needed
    ];

    // Define any relationships your Company has
    public function productItems()
    {
        return $this->hasMany(ProductItem::class);
    }

    // You can add more relationships and methods as needed
}
    