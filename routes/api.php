<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Controller Import
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AlamatController;
use App\Http\Controllers\Api\KeranjangController;
use App\Http\Controllers\Api\MidtransController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\PesananController;
use App\Http\Controllers\Api\ChatRoomController;
use App\Http\Controllers\Api\MessageController;

Route::prefix('v1')->group(function () {

    // ========================
    // 🔓 Rute Publik (Tanpa Token)
    // ========================
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/verify-payment/{order_id}', [MidtransController::class, 'verifyPaymentStatus']);

    // ✅ Callback dari Midtrans (tidak pakai auth)
    Route::post('/midtrans/callback', [MidtransController::class, 'handleCallback']);

    // ========================
    // 🔐 Rute Proteksi Token (Login Required)
    // ========================
    Route::middleware('auth:sanctum')->group(function () {

        // ===== 👤 User Info =====
        Route::get('/user', fn(Request $request) => $request->user());

        // ===== 🔧 Profil =====
        Route::post('/user/update', [UserController::class, 'update']);

        // ===== 📍 Alamat =====
        Route::get('/user/addresses', [AlamatController::class, 'index']);
        Route::post('/user/address', [AlamatController::class, 'store']);
        Route::put('/user/address/{id}', [AlamatController::class, 'update']);
        Route::delete('/user/address/{id}', [AlamatController::class, 'destroy']);
        Route::patch('/user/address/set-primary/{id}', [AlamatController::class, 'setPrimary']);

        // ===== 🛒 Keranjang =====
        Route::get('/keranjang', [KeranjangController::class, 'index']); // ambil dari token
        Route::post('/keranjang', [KeranjangController::class, 'store']);
        Route::put('/keranjang/{id}', [KeranjangController::class, 'update']);
        Route::delete('/keranjang/{id}', [KeranjangController::class, 'destroy']);

        // ===== 💳 Checkout & Pembayaran =====
        Route::post('/checkout', [CheckoutController::class, 'checkout']);
        Route::post('/midtrans/snap-token', [MidtransController::class, 'getSnapToken']); // ✅ tambahkan ini


        // ===== 📦 Pesanan =====
        Route::get('/pesanan', [PesananController::class, 'index']);
        Route::get('/pesanan/{id}', [PesananController::class, 'show']);
        Route::patch('/pesanan/{id}/cancel', [PesananController::class, 'cancel']);
        Route::patch('/pesanan/{id}/mark-received', [PesananController::class, 'markAsReceived']);

        // ===== 💬 Chat Room =====
        Route::get('/chat-rooms', [ChatRoomController::class, 'index']);
        Route::post('/chat-rooms', [ChatRoomController::class, 'store']);
        Route::get('/chat-rooms/{id}/messages', [MessageController::class, 'index']);
        Route::post('/chat-rooms/{id}/messages', [MessageController::class, 'store']);
        Route::post('/chat-rooms/{id}/upload', [MessageController::class, 'upload']);
        Route::post('/chat-rooms/{id}/read', [MessageController::class, 'markAsRead']);
        Route::get('/chat-rooms/unread', [MessageController::class, 'hasUnread']);


    });

});
