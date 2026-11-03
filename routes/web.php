<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommodityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Middleware\EnsureActive;
use App\Http\Middleware\EnsureOwner;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang', [CartController::class, 'add'])->name('cart.add');
Route::patch('/keranjang', [CartController::class, 'update'])->name('cart.update');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:10,1');

Route::get('/pesanan/{token}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/pesanan/{token}/bukti', [OrderController::class, 'proof'])->name('orders.proof')->middleware('throttle:10,1');
Route::get('/cek-pesanan', [OrderController::class, 'lookup'])->name('orders.lookup');
Route::post('/cek-pesanan', [OrderController::class, 'find'])->name('orders.find')->middleware('throttle:10,1');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// Semua admin dan staf (harus login dan akunnya aktif)
Route::middleware(['auth', EnsureActive::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('komoditas', CommodityController::class)
        ->parameters(['komoditas' => 'commodity'])
        ->except(['index', 'show']);

    Route::resource('kategori', CategoryController::class)
        ->parameters(['kategori' => 'category'])
        ->only(['index', 'store', 'edit', 'update', 'destroy']);

    Route::get('stok', [StockController::class, 'index'])->name('stok.index');
    Route::post('stok', [StockController::class, 'store'])->name('stok.store');

    Route::get('pesanan', [AdminOrderController::class, 'index'])->name('pesanan.index');
    Route::get('pesanan/{order}', [AdminOrderController::class, 'show'])->name('pesanan.show');
    Route::patch('pesanan/{order}/jumlah', [AdminOrderController::class, 'items'])->name('pesanan.items');
    Route::patch('pesanan/{order}/status', [AdminOrderController::class, 'status'])->name('pesanan.status');
    Route::get('pesanan/{order}/bukti', [AdminOrderController::class, 'proof'])->name('pesanan.bukti');

    Route::get('akun', [AccountController::class, 'edit'])->name('akun.edit');
    Route::put('akun', [AccountController::class, 'update'])->name('akun.update');

    // Khusus pemilik
    Route::middleware(EnsureOwner::class)->group(function () {
        Route::get('laporan', [ReportController::class, 'index'])->name('laporan.index');
        Route::get('laporan/unduh', [ReportController::class, 'csv'])->name('laporan.csv');

        Route::get('pengaturan', [SettingController::class, 'edit'])->name('pengaturan.edit');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');

        Route::resource('pengguna', UserController::class)
            ->parameters(['pengguna' => 'user'])
            ->except(['show', 'destroy']);
    });
});