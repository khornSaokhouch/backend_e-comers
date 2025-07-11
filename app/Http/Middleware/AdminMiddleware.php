<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Check if a user is authenticated
        // 2. If authenticated, check if their isAdmin() method returns true
        if (!$request->user() || !$request->user()->isAdmin()) { // <--- KEY CHANGE: Calling the isAdmin() method
            return response()->json(['message' => 'Unauthorized. Admins only.'], 403);
        }

        return $next($request);
    }
}
