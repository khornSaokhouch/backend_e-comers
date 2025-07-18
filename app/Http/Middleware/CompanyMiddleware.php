<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'company') {
            return response()->json(['message' => 'Access denied. Only company users allowed.'], 403);
        }

        return $next($request);
    }
}
