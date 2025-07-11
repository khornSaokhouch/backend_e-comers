<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\CategoryController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Social Login
Route::get('/auth/{provider}/redirect', [GoogleAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [GoogleAuthController::class, 'handleProviderCallback']);

// Facebook OAuth
Route::get('/auth/{provider}/redirect', [GoogleAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [GoogleAuthController::class, 'handleProviderCallback']);


// Email verification routes
Route::middleware('auth:api')->group(function () {

    Route::get('/user', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
    Route::get('/email/verify', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified']);
        }
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    // Check if the hash matches
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

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);  // Use PUT or PATCH
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
     // User management
     Route::get('/users', [UserController::class, 'index']);
     Route::get('/users/{id}', [UserController::class, 'show']);
     Route::post('/users/{id}', [UserController::class, 'update']);
     Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

