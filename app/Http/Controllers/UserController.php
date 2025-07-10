<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // List all users (admin only, ideally)
    public function index(Request $request)
    {
        // Check if authenticated user is admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden: Admins only'], 403);
        }
    
        // If admin, return all users
        $users = User::all();
        return response()->json($users);
    }
    

    // Show a single user by ID
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        $user->profile_image_url = $user->profile_image ? asset('storage/' . $user->profile_image) : null;

    
        return response()->json($user);
    }
    

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'sometimes|string|min:6|confirmed',
                'role' => 'sometimes|string|in:user,admin',
                'image' => 'sometimes|mimes:jpg,jpeg,png,gif,webp,bmp|max:2048',
            ]);
    
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
    
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('profile_images', 'public');
    
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
    
                $validated['profile_image'] = $path;
            }
    
            $user->update($validated);
            $user->refresh(); // reload fresh data
    
            $user->profile_image_url = $user->profile_image ? asset('storage/' . $user->profile_image) : null;
    
            return response()->json($user);
    
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['message' => 'Validation failed', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
        }
    }
    
    

    // Delete a user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }
}
