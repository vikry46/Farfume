<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\KariawanController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProdukMarketController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\SupllyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthenticateController;


Route::get('/csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});





// User Management
// Routes only accessible by Super Admin
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {

    //permissions
    Route::prefix('auth')->group(function () {
        Route::get('/permissions', [AuthenticateController::class, 'indexPermission']);
        Route::post('/permission/store', [AuthenticateController::class, 'storePermission']);
        Route::post('/role/store', [AuthenticateController::class, 'storeRole']);
        Route::post('/roles/{role}/permission', [AuthenticateController::class, 'givePermissionToRole']);
        Route::post('/users/{user}/role', [AuthenticateController::class, 'assignRoleToUser']);
        Route::post('/users/{user}/permission', [AuthenticateController::class, 'assignPermissionToUser']);
    });

    // User Management
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/user/store', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/update/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/role/{id}/assign-role', [UserController::class, 'assignRole']);
});

// Suplly
Route::middleware(['auth:sanctum', 'permission:index-supplies'])->get('supllies', [SupllyController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-supplies'])->post('suplly/store', [SupllyController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:show-supplies'])->get('suplly/show/{id}', [SupllyController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update-supplies'])->put('suplly/edit/{id}', [SupllyController::class, 'edit']);
Route::middleware(['auth:sanctum', 'permission:update-supplies'])->patch('suplly/update/{id}', [SupllyController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete-supplies'])->delete('suplly/delete/{id}', [SupllyController::class, 'delete']);

// Produk
Route::middleware(['auth:sanctum', 'permission:index-products'])->get('product', [ProdukController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-products'])->post('Product/store', [ProdukController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:show-products'])->get('Product/show/{id}', [ProdukController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update-products'])->put('Product/edit/{id}', [ProdukController::class, 'edit']);
Route::middleware(['auth:sanctum', 'permission:update-products'])->patch('Product/update/{id}', [ProdukController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete-products'])->delete('Product/delete/{id}', [ProdukController::class, 'delete']);

// Market
Route::middleware(['auth:sanctum', 'permission:index-markets'])->get('market', [MarketController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-markets'])->post('market/store', [MarketController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:show-markets'])->get('market/show/{id}', [MarketController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update-markets'])->put('market/edit/{id}', [MarketController::class, 'edit']);
Route::middleware(['auth:sanctum', 'permission:update-markets'])->patch('market/update/{id}', [MarketController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete-markets'])->delete('market/delete/{id}', [MarketController::class, 'delete']);

// Kariawan
Route::middleware(['auth:sanctum', 'permission:index-kariawan'])->get('kariawan', [KariawanController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-kariawan'])->post('kariawan/store', [KariawanController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:show-kariawan'])->get('kariawan/show/{id}', [KariawanController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update-kariawan'])->put('kariawan/edit/{id}', [KariawanController::class, 'edit']);
Route::middleware(['auth:sanctum', 'permission:update-kariawan'])->patch('kariawan/update/{id}', [KariawanController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete-kariawan'])->delete('kariawan/delete/{id}', [KariawanController::class, 'delete']);

// Pengiriman
Route::middleware(['auth:sanctum', 'permission:index-pengiriman'])->get('pengiriman', [PengirimanController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-pengiriman'])->post('pengiriman/store', [PengirimanController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:show-pengiriman'])->get('pengiriman/show/{id}', [PengirimanController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update-pengiriman'])->patch('pengiriman/update/{id}', [PengirimanController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete-pengiriman'])->delete('pengiriman/delete/{id}', [PengirimanController::class, 'destroy']);

// Penjualan
Route::middleware(['auth:sanctum', 'permission:index-penjualan'])->get('penjualan', [PenjualanController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-penjualan'])->post('penjualan/store', [PenjualanController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:show-penjualan'])->get('penjualan/show/{id}', [PenjualanController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update-penjualan'])->patch('penjualan/update/{id}', [PenjualanController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete-penjualan'])->delete('penjualan/delete/{id}', [PenjualanController::class, 'destroy']);

// Barang Masuk
Route::middleware(['auth:sanctum', 'permission:index-barangmasuk'])->get('barang-masuk', [BarangMasukController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-barangmasuk'])->post('barang-masuk/store', [BarangMasukController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:show-barangmasuk'])->get('barang-masuk/show/{id}', [BarangMasukController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:update-barangmasuk'])->patch('barang-masuk/update/{id}', [BarangMasukController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:delete-barangmasuk'])->delete('barang-masuk/delete/{id}', [BarangMasukController::class, 'destroy']);

// Relasi Market Produk
Route::middleware(['auth:sanctum', 'permission:index-marketproduk'])->get('market-produk', [RelationController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:create-marketproduk'])->post('market-produk/store', [RelationController::class, 'store']);

// Stok Market
Route::middleware(['auth:sanctum', 'permission:index-stokmarket'])->get('stok-market', [ProdukMarketController::class, 'index']);


Route::middleware(['auth:sanctum', 'role:user'])->group(function () {});
