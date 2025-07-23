<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
    ];

    // Relationship to Store
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
