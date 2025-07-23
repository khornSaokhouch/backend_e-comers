<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use App\Models\Store;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();

        // Custom route binding using seller_id instead of id
        Route::bind('store', function ($value) {
            return Store::where('seller_id', $value)->firstOrFail();
        });
    }
}
