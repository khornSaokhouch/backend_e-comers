<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'user_id'];

    // Relationships

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'store_id', 'id');
    }

    public function customers()
    {
        return $this->hasMany(CustomerList::class, 'store_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}
