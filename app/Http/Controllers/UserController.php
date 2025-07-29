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
    

    public function show(Request $request, $id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Optional: only allow self-view or admin
        if ($request->user()->id !== $user->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
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
    
        // Authorization check: Only the user themselves or an admin can update
        if ($request->user()->id !== $user->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden: You can only update your own profile unless you are an admin.'], 403);
        }
    
        try {
            $rules = [
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'sometimes|string|min:6|confirmed',
                'image' => 'sometimes|mimes:jpg,jpeg,png,gif,webp,bmp|max:2048',
            ];
    
            // Admins can update role — include 'admin' now
            if ($request->user()->isAdmin()) {
                $rules['role'] = 'sometimes|string|in:user,company,admin';
            } else {
                // Non-admins must not touch the role
                if ($request->has('role')) {
                    return response()->json(['message' => 'Forbidden: You are not authorized to change user roles.'], 403);
                }
            }
    
            $validated = $request->validate($rules);
    
            // Prevent demoting the last admin
            if (
                $request->user()->isAdmin() &&
                isset($validated['role']) &&
                $user->role === 'admin' &&
                $validated['role'] !== 'admin' &&
                User::where('role', 'admin')->count() === 1
            ) {
                return response()->json(['message' => 'You cannot demote the only remaining admin.'], 403);
            }
    
            // Hash new password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
    
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('profile_images', 'public');
    
                // Delete old image
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }
    
                $validated['profile_image'] = $path;
            }
    
            $user->update($validated);
            $user->refresh();
    
            $user->profile_image_url = $user->profile_image ? asset('storage/' . $user->profile_image) : null;
    
            return response()->json($user);
    
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['message' => 'Validation failed', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
        }
    }
    
    

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        // Only admin can delete users
        abort_unless($request->user()->isAdmin(), 403, 'Admins only can delete users.');
    
        // Prevent admin from deleting themselves
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Forbidden: You cannot delete your own admin account.'], 403);
        }
    
        // Delete profile image if it exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }
    
        $user->delete();
    
        return response()->json(['message' => 'User deleted successfully.']);
    }
}