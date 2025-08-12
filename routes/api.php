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
use App\Http\Controllers\ShopOrderController;
use App\Http\Controllers\UserPaymentMethodController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ShippingMethodController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\ProductItemController;
use App\Http\Controllers\OrderLineController;
use App\Http\Controllers\UserReviewController;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

use App\Http\Controllers\TelegramWebhookController;


// ------------------------------
// Public Routes
// ------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Social Login (Google/Facebook)
Route::get('/auth/{provider}/redirect', [GoogleAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [GoogleAuthController::class, 'handleProviderCallback']);

// Telegram Webhook
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

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

    // Category Routes (No authentication required)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Product Category Management
    Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

    // Payment Types
    Route::get('/payment-types', [PaymentTypeController::class, 'index']);
    Route::get('/payment-types/{id}', [PaymentTypeController::class, 'show']);

    // Shipping Methods

    Route::get('/shipping-methods', [ShippingMethodController::class, 'index']);
    Route::get('/shipping-methods/{id}', [ShippingMethodController::class, 'show']);

    // Shopping Order Management
    Route::get('/shop-orders', [ShopOrderController::class, 'index']);
    Route::get('/shop-orders/{id}', [ShopOrderController::class, 'show']);

    // order statuses
    Route::get('/order-statuses', [OrderStatusController::class, 'index']);
    Route::get('/order-statuses/{id}', [OrderStatusController::class, 'show']);

    // Store 
    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{id}', [StoreController::class, 'show']);

    // User Review
    Route::get('/user-reviews', [UserReviewController::class, 'index']);
    Route::get('/user-reviews/{id}', [UserReviewController::class, 'show']);

    // Promotion Category Routes

    Route::get('/promotion-category/{promotionId}/categories', [PromotionCategoryController::class, 'index']);
    Route::get('/promotion-category/{promotionId}/categories/{categoryId}', [PromotionCategoryController::class, 'show']);

    // Promotion Routes
    Route::get('/promotions', [PromotionController::class, 'index']);
    Route::get('/promotions/{id}', [PromotionController::class, 'show']);
        
    






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

    // Shopping Cart Items
    Route::get('/shopping-cart-items', [ShoppingCartItemController::class, 'index']);
    Route::get('/shopping-cart-items/{id}', [ShoppingCartItemController::class, 'show']);
    Route::post('/shopping-cart-items', [ShoppingCartItemController::class, 'store']);
    Route::put('/shopping-cart-items/{id}', [ShoppingCartItemController::class, 'update']);
    Route::delete('/shopping-cart-items/{id}', [ShoppingCartItemController::class, 'destroy']);
    Route::post('/shopping-cart-items/{id}/checkout', [ShoppingCartItemController::class, 'checkoutItem']);


    // Favourite Products
    Route::get('/favourites', [FavouriteController::class, 'index']);
    Route::get('/favourites/{id}', [FavouriteController::class, 'show']);
    Route::post('/favourites', [FavouriteController::class, 'store']);
    Route::delete('/favourites/{id}', [FavouriteController::class, 'destroy']);

    // User Review 

    Route::post('/user-reviews', [UserReviewController::class, 'store']);
    Route::put('/user-reviews/{id}', [UserReviewController::class, 'update']);

    Route::delete('/user-reviews/{id}', [UserReviewController::class, 'destroy']);


        // Shop Orders

    Route::post('/shop-orders', [ShopOrderController::class, 'store']);
    Route::get('/shop-orders/user/{userId}', [ShopOrderController::class, 'showOrdersByUser']);




    // User Payment Methods
    Route::get('/user-payment-methods', [UserPaymentMethodController::class, 'index']);
    Route::get('/user-payment-methods/{id}', [UserPaymentMethodController::class, 'show']);
    Route::post('/user-payment-methods', [UserPaymentMethodController::class, 'store']);
    Route::put('/user-payment-methods/{id}', [UserPaymentMethodController::class, 'update']);
    Route::delete('/user-payment-methods/{id}', [UserPaymentMethodController::class, 'destroy']);



    // Order Histories
    Route::get('/order-histories', [OrderHistoryController::class, 'index']);
    Route::get('/order-histories/{id}', [OrderHistoryController::class, 'show']);
    Route::post('/order-histories', [OrderHistoryController::class, 'store']);
    Route::put('/order-histories/{id}', [OrderHistoryController::class, 'update']);
    Route::delete('/order-histories/{id}', [OrderHistoryController::class, 'destroy']);





 

});

Route::middleware(['auth:api', 'allow.admin.or.company'])->group(function () {

    // User Management
    Route::get('/users', [UserController::class, 'index']);
    
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

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']); // or use PUT/PATCH
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


    // store 

    // Route::post('/stores', [StoreController::class, 'store']);
    // Route::put('/stores/{id}', [StoreController::class, 'update']);
    // Route::delete('/stores/{id}', [StoreController::class, 'destroy']);
    Route::post('/stores', [StoreController::class, 'store']);
    Route::put('/stores/{id}', [StoreController::class, 'update']);
    Route::delete('/stores/{id}', [StoreController::class, 'destroy']);
    Route::get('/stores/user/{id}', [StoreController::class, 'getStoresByUserId']);



    // Payment Types

    Route::post('/payment-types', [PaymentTypeController::class, 'store']);
    Route::put('/payment-types/{id}', [PaymentTypeController::class, 'update']);
    Route::delete('/payment-types/{id}', [PaymentTypeController::class, 'destroy']);

    // shop order
    Route::put('/shop-orders/{id}', [ShopOrderController::class, 'update']);
    Route::delete('/shop-orders/{id}', [ShopOrderController::class, 'destroy']);
    Route::patch('/shop-orders/{id}/status', [ShopOrderController::class, 'updateStatus']); // ✅ NEW


    // Order Lines
    Route::get('/order-lines', [OrderLineController::class, 'index']);
    Route::get('/order-lines/{id}', [OrderLineController::class, 'show']);
    Route::post('/order-lines', [OrderLineController::class, 'store']);
    Route::put('/order-lines/{id}', [OrderLineController::class, 'update']);
    Route::delete('/order-lines/{id}', [OrderLineController::class, 'destroy']);



    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);      

    // Shipping Methods

    Route::post('/shipping-methods', [ShippingMethodController::class, 'store']);
    Route::put('/shipping-methods/{id}', [ShippingMethodController::class, 'update']);
    Route::delete('/shipping-methods/{id}', [ShippingMethodController::class, 'destroy']);
        
    // Order Statuses

    Route::post('/order-statuses', [OrderStatusController::class, 'store']);
    Route::put('/order-statuses/{id}', [OrderStatusController::class, 'update']);
    Route::delete('/order-statuses/{id}', [OrderStatusController::class, 'destroy']);  
    

    // Promotion Routes

    Route::post('/promotions', [PromotionController::class, 'store']);
    Route::put('/promotions/{id}', [PromotionController::class, 'update']);
    Route::delete('/promotions/{id}', [PromotionController::class, 'destroy']);

    // Promotion-Category Routes
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
