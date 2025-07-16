<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

// ------------------------------
// Public Routes
// ------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Social Login (Google/Facebook)
Route::get('/auth/{provider}/redirect', [GoogleAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [GoogleAuthController::class, 'handleProviderCallback']);

// ------------------------------
// Email Verification Routes
// ------------------------------
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified']);
    }

    $user->markEmailAsVerified();
    event(new Verified($user));

    return response()->json(['message' => 'Email verified!']);
})->middleware('signed')->name('verification.verify');


// ------------------------------
// Authenticated Routes (Normal Users)
// ------------------------------
Route::middleware('auth:api')->group(function () {
    // Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Email verification trigger
    Route::get('/email/verify', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified']);
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent']);
    });

    // View/update own user data
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users/{id}', [UserController::class, 'update']);
});


// ------------------------------
// Admin-Only Routes
// ------------------------------
Route::middleware(['auth:api', 'admin'])->group(function () {
    // User Management
    Route::get('/users', [UserController::class, 'index']);  // List all users (admin only)
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Category Management
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']); // or use PUT/PATCH
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});
