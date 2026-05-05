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
use App\Http\Controllers\Admin\AdminStockController;
use App\Http\Controllers\Admin\AdminReceiptController;
use App\Http\Controllers\Admin\AdminBalanceSheetController;
use App\Http\Controllers\Admin\AdminStoreInfoController;
use App\Http\Controllers\Admin\AdminRmaController;
use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AccessLevelController;
use App\Http\Controllers\Admin\AdminAdvertisementController;
use App\Http\Controllers\Admin\AdminNewsletterController;

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
    Route::get('/orders-export/trial-balance', [AdminOrderController::class, 'trialBalance']);
    Route::post('/orders/add-extra-product/{orderId}', [AdminOrderController::class, 'addExtraProduct']);

    // Member Management (Newly Added)
    Route::get('/members', [AdminMemberController::class, 'index']);
    Route::get('/members/{id}', [AdminMemberController::class, 'show']);
    Route::post('/members/store', [AdminMemberController::class, 'store']);
    Route::post('/members/update/{id}', [AdminMemberController::class, 'update']);
    Route::post('/members/toggle-b2b-approval/{id}', [AdminMemberController::class, 'toggleB2bApproval']);
    Route::delete('/members/delete/{id}', [AdminMemberController::class, 'destroy']);

    // Admin User Management
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::post('/users/store', [AdminUserController::class, 'store']);
    Route::post('/users/update/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/delete/{id}', [AdminUserController::class, 'destroy']);

    // Access Level Management
    Route::get('/access-levels', [AccessLevelController::class, 'index']);
    Route::get('/access-levels/{id}', [AccessLevelController::class, 'show']);
    Route::post('/access-levels/store', [AccessLevelController::class, 'store']);
    Route::post('/access-levels/update/{id}', [AccessLevelController::class, 'update']);
    Route::delete('/access-levels/delete/{id}', [AccessLevelController::class, 'destroy']);

    // Supplier Management
    Route::get('/suppliers', [AdminSupplierController::class, 'index']);
    Route::get('/suppliers/{id}', [AdminSupplierController::class, 'show']);
    Route::post('/suppliers/store', [AdminSupplierController::class, 'store']);
    Route::post('/suppliers/update/{id}', [AdminSupplierController::class, 'update']);
    Route::delete('/suppliers/delete/{id}', [AdminSupplierController::class, 'destroy']);

    // Expense & VAT Management (Newly Added)
    Route::get('/expenses', [AdminExpenseController::class, 'index']);
    Route::post('/expenses/toggle-status', [AdminExpenseController::class, 'toggleStatus']);

    // Stock Management
    Route::get('/stock', [AdminStockController::class, 'index']);

    // Receipt Management
    Route::get('/receipts', [AdminReceiptController::class, 'index']);
    Route::post('/receipts/store', [AdminReceiptController::class, 'store']);
    Route::get('/receipts/{id}/print', [AdminReceiptController::class, 'printReceipt']);

    // Balance Sheets
    Route::get('/balance-sheets/{quarter}', [AdminBalanceSheetController::class, 'getQuarterlyData']);

    // Store Info
    Route::get('/store-info', [AdminStoreInfoController::class, 'index']);
    Route::post('/store-info/update', [AdminStoreInfoController::class, 'update']);

    // RMA History
    Route::get('/rma-history', [AdminRmaController::class, 'index']);
    Route::get('/rma-history/{id}', [AdminRmaController::class, 'show']);
    Route::post('/rma-history/update/{id}', [AdminRmaController::class, 'update']);
    Route::post('/rma-history/toggle-delivered/{id}', [AdminRmaController::class, 'toggleDelivered']);

    // Advertisement Management
    Route::get('/advertisements', [AdminAdvertisementController::class, 'index']);
    Route::get('/advertisements/{id}', [AdminAdvertisementController::class, 'show']);
    Route::post('/advertisements/store', [AdminAdvertisementController::class, 'store']);
    Route::post('/advertisements/update/{id}', [AdminAdvertisementController::class, 'update']);
    Route::post('/advertisements/toggle-status/{id}', [AdminAdvertisementController::class, 'toggleStatus']);
    Route::delete('/advertisements/delete/{id}', [AdminAdvertisementController::class, 'destroy']);
    Route::get('/advertisements/location/{location}', [AdminAdvertisementController::class, 'getByLocation']);

    // Newsletter Management
    Route::get('/newsletters', [AdminNewsletterController::class, 'index']);
    Route::get('/newsletters/{id}', [AdminNewsletterController::class, 'show']);
    Route::post('/newsletters/store', [AdminNewsletterController::class, 'store']);
    Route::delete('/newsletters/delete/{id}', [AdminNewsletterController::class, 'destroy']);
    Route::delete('/newsletters/delete-multiple', [AdminNewsletterController::class, 'destroyMultiple']);

    // Logo Management
    Route::get('/logo', [LogoController::class, 'index']);
    Route::post('/logo/update', [LogoController::class, 'update']);
});
