<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\RegistrationController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminSupplierController;
use App\Http\Controllers\Admin\AdminMemberController;
use App\Http\Controllers\Admin\AdminExpenseController;
use App\Http\Controllers\Admin\AdminMenuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Auth Routes ---
Route::post('/login/member', [AuthController::class, 'memberLogin']);
Route::post('/register/member', [AuthController::class, 'register']);
Route::post('/login/admin', [AuthController::class, 'adminLogin']);
Route::post('/logout', [AuthController::class, 'logout']);

// --- Frontend Routes ---
Route::prefix('frontend')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories', [ProductController::class, 'categories']);
    
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/add', [CartController::class, 'addItem']);
    Route::post('/cart/remove', [CartController::class, 'removeItem']);
    Route::post('/cart/clear', [CartController::class, 'clearCart']);
    
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
    
    Route::post('/register/member', [RegistrationController::class, 'registerMember']);
    Route::post('/register/wholesaler', [RegistrationController::class, 'registerWholesaler']);
    Route::get('/register/confirm', [RegistrationController::class, 'confirm'])->name('register.confirm');
    
    Route::get('/account', [AccountController::class, 'index']);
    Route::post('/account/update', [AccountController::class, 'update']);
    Route::get('/clients', [AccountController::class, 'getClients']);
    Route::get('/orders', [AccountController::class, 'getOrders']);

    Route::post('/checkout/save', [CheckoutController::class, 'saveOrder']);
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');
});

// --- Admin Routes ---
Route::prefix('admin')->group(function () {
    // Product Management
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::get('/dashboard-data', [AdminProductController::class, 'dashboardData']);
    Route::post('/products/store', [AdminProductController::class, 'store']);
    Route::post('/products/update/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/delete/{id}', [AdminProductController::class, 'destroy']);
    
    // Menu Management
    Route::get('/menu', [AdminMenuController::class, 'index']);
    Route::get('/menu/{id}', [AdminMenuController::class, 'show']);
    Route::post('/menu/store', [AdminMenuController::class, 'store']);
    Route::post('/menu/update/{id}', [AdminMenuController::class, 'update']);
    Route::delete('/menu/delete/{id}', [AdminMenuController::class, 'destroy']);
    
    // Order Management
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::post('/orders/toggle-cancel/{id}', [AdminOrderController::class, 'toggleCancel']);
    Route::post('/orders/toggle-shipping/{id}', [AdminOrderController::class, 'toggleShipping']);
    Route::post('/orders/toggle-status/{id}', [AdminOrderController::class, 'toggleStatus']);
    Route::get('/orders/invoice/{id}', [AdminOrderController::class, 'invoice']);
    Route::get('/orders/pdf/{id}', [AdminOrderController::class, 'pdf']);
    Route::get('/orders-export/pdf', [AdminOrderController::class, 'exportPdf']);
    Route::get('/orders-export/trial-balance', [AdminOrderController::class, 'trialBalance']);
    Route::post('/orders/add-extra-product/{orderId}', [AdminOrderController::class, 'addExtraProduct']);
    
    // Supplier Management
    Route::get('/suppliers', [AdminSupplierController::class, 'index']);
    Route::post('/suppliers/store', [AdminSupplierController::class, 'store']);
    Route::post('/suppliers/update/{id}', [AdminSupplierController::class, 'update']);

    // Member Management (Newly Added)
    Route::get('/members', [AdminMemberController::class, 'index']);
    Route::post('/members/update/{id}', [AdminMemberController::class, 'update']);
    Route::delete('/members/delete/{id}', [AdminMemberController::class, 'destroy']);

    // Expense & VAT Management (Newly Added)
    Route::get('/expenses', [AdminExpenseController::class, 'index']);
    Route::post('/expenses/toggle-status', [AdminExpenseController::class, 'toggleStatus']);
});
