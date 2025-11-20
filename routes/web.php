<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\OrdersImportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StocksImportController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ================== PUBLIC ROUTES ==================
Route::get('/', function () {
    return redirect()->route('login.view');
});
// Route::get('/monitoring', [MainController::class, 'monitoring'])->name('monitoring');
Route::get('/monitoring', [MainController::class, 'monitoring'])
    ->name('monitoring')
    ->middleware('monitoring.access'); // ← INI YANG PENTING
Route::get('/monitoring/export', [MainController::class, 'exportMonitoring'])
    ->name('monitoring.export')
    ->middleware('monitoring.access');
Route::post('/monitoring/verify', function (Request $request) {
    $request->validate([
        'monitoring_password' => 'required'
    ]);

    // Ganti dengan password yang kamu mau (bisa dari .env)
    $correct = env('MONITORING_PASSWORD', 'kyb2025');

    if ($request->monitoring_password === $correct) {
        session(['monitoring_access_granted' => true]);
        return response()->json(['success' => true]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Password monitoring salah!'
    ]);
})->name('monitoring.verify');


// Login Routes
Route::get('/login', [AuthController::class, 'index'])->name('login.view');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// OTP
Route::get('/otp', [MainController::class, 'otp'])->name('formotp');
Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');
//resend OTP
Route::post('/otp/resend', [MainController::class, 'resendOtp'])->name('otp.resend');

// Captcha refresh
Route::get('/reload-captcha', function () {
    return response()->json(['captcha' => captcha_img('flat')]);
})->name('reload.captcha');

// ================== PROTECTED ROUTES (LOGIN REQUIRED) ==================
Route::middleware(['auth'])->group(function () {
    Route::get('/export', [MainController::class, 'export'])->name('export');
    Route::get('/report', [MainController::class, 'report'])->name('report');
    Route::get('/import', [StocksImportController::class, 'importView'])->name('import.view');
    Route::post('/import', [StocksImportController::class, 'import'])->name('import');
    Route::get('order-view', [OrdersImportController::class, 'orderView'])->name('order-view');
    Route::post('import-orders', [OrdersImportController::class, 'import'])->name('import-orders');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 🔥 TAMBAH: Route export monitoring
    // Route::get('/monitoring/export', [MainController::class, 'exportMonitoring'])->name('monitoring.export');
});

//================= PROTECTED ROUTES (SUPERADMIN ONLY) ==================
// Routes yang hanya bisa diakses Super Admin
Route::middleware(['auth', 'superadmin'])->group(function () {
    // Route::get('/dashboard-d', function () {
    //     return view('dashboard');
    // })->name('dashboard');

    Route::get('/import', [\App\Http\Controllers\StocksImportController::class, 'importView'])->name('import.view');
    Route::post('/import', [\App\Http\Controllers\StocksImportController::class, 'import'])->name('import');
    Route::get('/report', [\App\Http\Controllers\MainController::class, 'report'])->name('report');
});

// Hapus route berikut karena StockController tidak ada:
// Route::get('/import', [StockController::class, 'importView'])->name('import.view');
// Route::post('/import', [StockController::class, 'import'])->name('import');

Route::get('/order-template/{type?}', [OrdersImportController::class, 'orderTemplate'])
    ->name('orders-template')
    ->where('type', 'empty|with-data'); // opsional, biar aman
