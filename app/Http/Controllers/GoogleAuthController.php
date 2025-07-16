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

            // Find user by Google ID or email
            $user = User::where('google_id', $socialUser->getId())
                        ->orWhere('email', $socialUser->getEmail())
                        ->first();

            if (!$user) {
                // Create new user with Google info
                $user = User::create([
                    'google_id' => $socialUser->getId(),
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'role' => 'user',
                    'email_verified_at' => now(),
                    'profile_image' => $socialUser->getAvatar(),
                    'google_avatar' => $socialUser->getAvatar(),  // Optional: store original Google avatar separately
                    'password' => Hash::make(Str::random(16)),
                ]);
            } else {
                // Existing user — update google_id if missing
                if (!$user->google_id) {
                    $user->google_id = $socialUser->getId();
                }

                // Update google_avatar to keep original Google image up-to-date without overwriting user custom profile_image
                $user->google_avatar = $socialUser->getAvatar();

                $user->save();
            }

            $token = JWTAuth::fromUser($user);

            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            return redirect()->away($frontendUrl . '/auth/callback?token=' . $token);
        } catch (\Exception $e) {
            Log::error("Socialite callback error for provider {$provider}: " . $e->getMessage());
            return response()->json(['message' => 'Authentication failed. Please try again.', 'error' => $e->getMessage()], 401);
        }
    }
}