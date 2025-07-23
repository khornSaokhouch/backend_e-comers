<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowAdminOrCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
    
        if (!$user || !in_array($user->role, ['admin', 'company'])) {
            return response()->json(['message' => 'Access denied. Admins or Company only.'], 403);
        }
    
        return $next($request);
    }
    
}
