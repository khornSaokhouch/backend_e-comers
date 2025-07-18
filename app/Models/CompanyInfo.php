<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyInfo extends Model
{
    use HasFactory;

    protected $table = 'companies_info'; // specify table name

    protected $fillable = [
        'user_id',
        'company_name',
        'company_image',
        'description',
        'website_url',
        'business_hours',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'address',
        'city',
        'country',
    ];

    protected $appends = ['company_image_url'];

public function getCompanyImageUrlAttribute()
{
    return $this->company_image 
        ? asset('storage/' . $this->company_image)
        : null;
}


    // Optional: Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
