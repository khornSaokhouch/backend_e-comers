<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden: Admins only'], 403);
        }

        $disk = App::environment('local') ? 'public' : 'b2';

        $users = User::all()->map(function ($user) use ($disk) {
            $user->profile_image_url = $user->profile_image
                ? $this->generateImageUrl($user->profile_image, $disk)
                : null;
            return $user;
        });

        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($request->user()->id !== $user->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $disk = App::environment('local') ? 'public' : 'b2';
        $user->profile_image_url = $user->profile_image
            ? $this->generateImageUrl($user->profile_image, $disk)
            : null;

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($request->user()->id !== $user->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $disk = App::environment('local') ? 'public' : 'b2';

        try {
            $rules = [
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'sometimes|string|min:6|confirmed',
                'image' => 'sometimes|image|max:2048',
            ];

            if ($request->user()->isAdmin()) {
                $rules['role'] = 'sometimes|string|in:user,company,admin';
            } elseif ($request->has('role')) {
                return response()->json(['message' => 'Forbidden: You are not authorized to change user roles.'], 403);
            }

            $validated = $request->validate($rules);

            if (
                $request->user()->isAdmin() &&
                isset($validated['role']) &&
                $user->role === 'admin' &&
                $validated['role'] !== 'admin' &&
                User::where('role', 'admin')->count() === 1
            ) {
                return response()->json(['message' => 'You cannot demote the only remaining admin.'], 403);
            }

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            if ($request->hasFile('image')) {
                if ($user->profile_image) {
                    Storage::disk($disk)->delete($user->profile_image);
                }

                $path = $request->file('image')->store('profile_images', $disk);
                $validated['profile_image'] = $path;
            }

            $user->update($validated);
            $user->refresh();

            $user->profile_image_url = $user->profile_image
                ? $this->generateImageUrl($user->profile_image, $disk)
                : null;

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

        abort_unless($request->user()->isAdmin(), 403, 'Admins only can delete users.');

        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Forbidden: You cannot delete your own admin account.'], 403);
        }

        $disk = App::environment('local') ? 'public' : 'b2';

        if ($user->profile_image && Storage::disk($disk)->exists($user->profile_image)) {
            Storage::disk($disk)->delete($user->profile_image);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    private function generateImageUrl($path, $disk)
    {
        if ($disk === 'public') {
            return asset('storage/' . $path);
        }
    
        return Storage::disk('b2')->temporaryUrl($path, now()->addMinutes(60));
    }
    
}
