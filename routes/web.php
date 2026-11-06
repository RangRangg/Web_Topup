<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameAccountController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JokiController;
use App\Http\Controllers\JokiDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\TopUpController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Aethereal Joki
|--------------------------------------------------------------------------
*/

// Public Frontend Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/joki', [JokiController::class, 'index'])->name('joki');
Route::get('/joki/checkout', [JokiController::class, 'checkout'])->name('joki.checkout');
Route::post('/joki/checkout', [JokiController::class, 'processCheckout'])
    ->name('joki.checkout.store')
    ->middleware('throttle:15,1');
Route::get('/joki/{service:slug}', [JokiController::class, 'show'])
    ->name('joki.show')
    ->where('service', '^(?!dashboard|checkout).*$');
Route::get('/topup', [TopUpController::class, 'index'])->name('topup');
Route::get('/topup/{game:slug}', [TopUpController::class, 'game'])->name('topup.game');
Route::match(['get', 'post'], '/api/check-game-account', [GameAccountController::class, 'check'])
    ->name('api.check-account');
Route::get('/games', [GameController::class, 'index'])->name('games');
Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('games.show');

// AI Assistant (Aethereal AI)
Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai-assistant');
Route::post('/ai-assistant/consult', [AiAssistantController::class, 'consult'])
    ->name('ai-assistant.consult')
    ->middleware('throttle:30,1');
Route::get('/ai/history', [AiAssistantController::class, 'history'])->name('ai.history');
Route::post('/ai/clear', [AiAssistantController::class, 'clear'])->name('ai.clear');

// Top Up & Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->name('checkout.store')
    ->middleware('throttle:15,1');

// Payment Gateway Webhook (CSRF exempt, signature protected, idempotent)
Route::post('/webhooks/payment', [PaymentWebhookController::class, 'handle'])
    ->name('webhooks.payment')
    ->middleware('throttle:60,1');

// Orders & Tracking
Route::get('/order/checkout/{package:slug}', [OrderController::class, 'checkout'])->name('orders.checkout');
Route::post('/order', [OrderController::class, 'store'])->name('orders.store');
Route::get('/tracking', [OrderController::class, 'track'])->name('tracking');
Route::get('/order/{code}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/order/{code}/wa/{target}', [OrderController::class, 'waRedirect'])->name('orders.wa-redirect');
Route::post('/order/{code}/proof', [OrderController::class, 'uploadProof'])->name('orders.upload-proof');
Route::post('/order/{order:order_code}/simulate-pay', [CheckoutController::class, 'simulatePay'])->name('orders.simulate-pay');

// Authenticated Order Actions (Review, Claim, Progress)
Route::middleware('auth')->group(function () {
    Route::post('/order/{order:order_code}/review', [OrderController::class, 'storeReview'])->name('orders.review');
    Route::post('/order/{order:order_code}/claim', [OrderController::class, 'claim'])->name('orders.claim');
    Route::patch('/order/{order:order_code}/progress', [OrderController::class, 'updateProgress'])->name('orders.progress');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected User Dashboard & Admin
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [DashboardController::class, 'showOrder'])->name('orders.show');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::put('/profile/password', [DashboardController::class, 'updatePassword'])->name('profile.password');
        Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');
        Route::patch('/notifications/{notification}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    });

    // Joki Booster Workstation Dashboard (Prefix: /joki/dashboard, Role: joki)
    Route::get('/joki/dashboard', [JokiDashboardController::class, 'index'])->name('joki.dashboard')->middleware('role:joki');
    Route::prefix('joki/dashboard')->name('joki.dashboard.')->middleware('role:joki')->group(function () {
        Route::get('/orders', [JokiDashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [JokiDashboardController::class, 'showOrder'])->name('orders.show');
        Route::post('/orders/{order}/claim', [JokiDashboardController::class, 'claimOrder'])->name('orders.claim');
        Route::patch('/orders/{order}/progress', [JokiDashboardController::class, 'updateProgress'])->name('orders.progress');
        Route::get('/profile', [JokiDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [JokiDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('/earnings', [JokiDashboardController::class, 'earnings'])->name('earnings');
    });

    // Admin Control Suite (Role: admin)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');

        // Users Management
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('users.status');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');

        // Games CRUD
        Route::get('/games', [AdminController::class, 'games'])->name('games');
        Route::post('/games', [AdminController::class, 'storeGame'])->name('games.store');
        Route::put('/games/{game}', [AdminController::class, 'updateGame'])->name('games.update');
        Route::delete('/games/{game}', [AdminController::class, 'destroyGame'])->name('games.destroy');
        Route::patch('/games/{game}/status', [AdminController::class, 'toggleGameStatus'])->name('games.status');

        // Game Products CRUD
        Route::get('/products', [AdminController::class, 'products'])->name('products');
        Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
        Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{product}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
        Route::patch('/products/{product}/status', [AdminController::class, 'toggleProductStatus'])->name('products.status');

        // Joki Services CRUD
        Route::get('/joki-services', [AdminController::class, 'jokiServices'])->name('joki-services');
        Route::post('/joki-services', [AdminController::class, 'storeJokiService'])->name('joki-services.store');
        Route::put('/joki-services/{service}', [AdminController::class, 'updateJokiService'])->name('joki-services.update');
        Route::delete('/joki-services/{service}', [AdminController::class, 'destroyJokiService'])->name('joki-services.destroy');
        Route::patch('/joki-services/{service}/status', [AdminController::class, 'toggleJokiServiceStatus'])->name('joki-services.status');

        // Orders Management
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AdminController::class, 'showOrder'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');
        Route::post('/orders/{order}/assign', [AdminController::class, 'assignBooster'])->name('orders.assign');

        // Payments Oversight
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
        Route::patch('/payments/{payment}/status', [AdminController::class, 'updatePaymentStatus'])->name('payments.status');

        // Promotions CRUD
        Route::get('/promotions', [AdminController::class, 'promotions'])->name('promotions');
        Route::post('/promotions', [AdminController::class, 'storePromotion'])->name('promotions.store');
        Route::put('/promotions/{promotion}', [AdminController::class, 'updatePromotion'])->name('promotions.update');
        Route::delete('/promotions/{promotion}', [AdminController::class, 'destroyPromotion'])->name('promotions.destroy');
        Route::patch('/promotions/{promotion}/status', [AdminController::class, 'togglePromotionStatus'])->name('promotions.status');

        // Reports & Analytics
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');

        // Audit Logs Viewer
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');

        // Telegram Bot API Integration
        Route::get('/telegram', [AdminController::class, 'telegram'])->name('telegram');
        Route::post('/telegram', [AdminController::class, 'updateTelegram'])->name('telegram.update');
        Route::post('/telegram/test', [AdminController::class, 'testTelegram'])->name('telegram.test');
        Route::post('/telegram/detect-chats', [AdminController::class, 'detectTelegramChats'])->name('telegram.detect-chats');
    });
});
