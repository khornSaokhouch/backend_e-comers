<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profile_images', 'public');
        }
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'profile_image' => $imagePath,
            'role' => $request->input('role', 'user'), // default to 'user'

        ]);
    
        $token = JWTAuth::fromUser($user);
    
        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    // Login user and create token
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = auth()->user();

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    // Logout user (invalidate token)
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'User successfully logged out.'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Failed to logout, please try again.'
            ], 500);
        }
    }



    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'token' => $newToken,
                'user' => auth()->user(),
                'message' => 'Token refreshed successfully.'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token refresh failed.',
            ], 401);
        }
    }

    // Get logged in user profile
    public function profile(Request $request)
{
    return response()->json([
        'user' => auth()->user()
    ]);
}

}
