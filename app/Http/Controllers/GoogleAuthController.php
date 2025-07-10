<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log; // ✅ Correct import

class GoogleAuthController extends Controller
{
    public function redirectToProvider(string $provider)
    {
        // Validate that the provider is one we support
        if (!in_array($provider, ['google'])) {
            return response()->json(['message' => 'Provider not supported.'], 422);
        }

        return Socialite::driver('google')->stateless()->redirect();
    }
    
    public function handleProviderCallback(string $provider)
    {
        if ($provider !== 'google') {
            return response()->json(['message' => 'Provider not supported.'], 422);
        }
    
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        
            $user = User::updateOrCreate(
                ['google_id' => $socialUser->getId()],  // match your table column
                [
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'role' => 'user',
                    'email_verified_at' => now(),
                    'profile_image' => $socialUser->getAvatar(),
                    'password' => Hash::make(Str::random(16)), // random password
                ]
            );
        
            $token = JWTAuth::fromUser($user);
        
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            return redirect()->away($frontendUrl . '/auth/callback?token=' . $token);
        } catch (\Exception $e) {
            Log::error("Socialite callback error for provider {$provider}: " . $e->getMessage());
            return response()->json(['message' => 'Authentication failed. Please try again.', 'error' => $e->getMessage()], 401);
        }

    }
    
}
