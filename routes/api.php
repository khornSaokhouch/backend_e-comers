<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CustomerListController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\PromotionCategoryController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ShoppingCartItemController;
use App\Http\Controllers\ShoppingCartController;
use App\Http\Controllers\ProductItemController;
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


// Make public

// Product Routes (No authentication required)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);



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

    //Become Sellers   
    Route::post('/sellers', [SellerController::class, 'store']); // Create a new seller
    
    // Shopping Cart Management
    Route::get('/shopping-carts', [ShoppingCartController::class, 'index']);
    Route::get('/shopping-carts/{id}', [ShoppingCartController::class, 'show']);
    Route::post('/shopping-carts', [ShoppingCartController::class, 'store']);
    Route::put('/shopping-carts/{id}', [ShoppingCartController::class, 'update']);
    Route::delete('/shopping-carts/{id}', [ShoppingCartController::class, 'destroy']);

         // Shopping Cart Items Management
    Route::get('/shopping-cart-items', [ShoppingCartItemController::class, 'index']);
    Route::get('/shopping-cart-items/{id}', [ShoppingCartItemController::class, 'show']);
    Route::post('/shopping-cart-items', [ShoppingCartItemController::class, 'store']);
    Route::put('/shopping-cart-items/{id}', [ShoppingCartItemController::class, 'update']);
    Route::delete('/shopping-cart-items/{id}', [ShoppingCartItemController::class, 'destroy']);


    // Favourite Products
    Route::get('/favourites', [FavouriteController::class, 'index']);
    Route::get('/favourites/{id}', [FavouriteController::class, 'show']);
    Route::post('/favourites', [FavouriteController::class, 'store']);
    Route::delete('/favourites/{id}', [FavouriteController::class, 'destroy']);

});

Route::middleware(['auth:api', 'allow.admin.or.company'])->group(function () {

    // User Management
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Company Profile Management
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/companies/{id}', [CompanyController::class, 'show']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::put('/companies/{id}', [CompanyController::class, 'update']);
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy']);



    // Product Management
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Product Category Management
    Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

    // Seller Management
    Route::get('/sellers', [SellerController::class, 'index']);
    Route::put('/sellers/{id}/approve', [SellerController::class, 'approve']);
    Route::put('/sellers/{id}/reject', [SellerController::class, 'reject']);
    Route::delete('/sellers/{id}', [SellerController::class, 'destroy']);

    // product item management
    Route::get('/product-items', [ProductItemController::class, 'index']);
    Route::get('/product-items/{id}', [ProductItemController::class, 'show']);
    Route::post('/product-items', [ProductItemController::class, 'store']);
    Route::put('/product-items/{id}', [ProductItemController::class, 'update']);
    Route::delete('/product-items/{id}', [ProductItemController::class, 'destroy']);

    // Category Management
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']); // or use PUT/PATCH
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Store Routes
    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{id}', [StoreController::class, 'show']);
    Route::post('/stores', [StoreController::class, 'store']);
    Route::put('/stores/{id}', [StoreController::class, 'update']);
    Route::delete('/stores/{id}', [StoreController::class, 'destroy']);



    // Promotion Routes
    Route::get('/promotions', [PromotionController::class, 'index']);
    Route::post('/promotions', [PromotionController::class, 'store']);
    Route::get('/promotions/{id}', [PromotionController::class, 'show']);
    Route::put('/promotions/{id}', [PromotionController::class, 'update']);
    Route::delete('/promotions/{id}', [PromotionController::class, 'destroy']);

    // Promotion-Category Routes
    Route::get('/promotion-category/{promotionId}/categories', [PromotionCategoryController::class, 'index']);
    Route::get('/promotion-category/{promotionId}/categories/{categoryId}', [PromotionCategoryController::class, 'show']);
    Route::post('/promotion-category/{promotionId}/categories', [PromotionCategoryController::class, 'store']);
    Route::put('/promotion-category/{promotionId}/categories/{categoryId}', [PromotionCategoryController::class, 'update']);
    Route::delete('/promotion-category/{promotionId}/categories/{categoryId}', [PromotionCategoryController::class, 'destroy']);

    // Stock Routes
    Route::get('/stocks', [StockController::class, 'index']);
    Route::get('/stocks/{id}', [StockController::class, 'show']);
    Route::post('/stocks', [StockController::class, 'store']);
    Route::put('/stocks/{id}', [StockController::class, 'update']);
    Route::delete('/stocks/{id}', [StockController::class, 'destroy']);

    // CustomerList Routes
    Route::get('/customers', [CustomerListController::class, 'index']);
    Route::get('/customers/{customer}', [CustomerListController::class, 'show']);
    Route::post('/customers', [CustomerListController::class, 'store']);
    Route::put('/customers/{customer}', [CustomerListController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerListController::class, 'destroy']);

    // Admin Notifications
    Route::get('/notifications', [AdminNotificationController::class, 'adminNotifications']);
});
