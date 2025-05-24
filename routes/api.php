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

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class,'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes only accessible by Super Admin
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {

    // User Management
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/user/store', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/update/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/role/{id}/assign-role', [UserController::class, 'assignRole']);

    // Suplly
    Route::get('supllies', [SupllyController::class, 'index']);
    Route::post('suplly/store', [SupllyController::class, 'store']);
    Route::get('suplly/show/{id}', [SupllyController::class, 'show']);
    Route::put('suplly/edit/{id}', [SupllyController::class, 'edit']);
    Route::patch('suplly/update/{id}', [SupllyController::class, 'update']);
    Route::delete('suplly/delete/{id}', [SupllyController::class, 'delete']);

    // Produk
    Route::get('Product', [ProdukController::class,'index']);
    Route::post('Product/store', [ProdukController::class,'store']);
    Route::get('Product/show/{id}', [ProdukController::class,'show']);
    Route::put('Product/edit/{id}', [ProdukController::class,'edit']);
    Route::patch('Product/update/{id}', [ProdukController::class,'update']);
    Route::delete('Product/delete/{id}', [ProdukController::class,'delete']);

    // Market
    Route::get('market', [MarketController::class,'index']);
    Route::post('market/store', [MarketController::class,'store']);
    Route::get('market/show/{id}', [MarketController::class,'show']);
    Route::put('market/edit/{id}', [MarketController::class,'edit']);
    Route::patch('market/update/{id}', [MarketController::class,'update']);
    Route::delete('market/delete/{id}', [MarketController::class,'delete']);

    // Kariawan
    Route::get('kariawan',[KariawanController::class,'index']);
    Route::post('kariawan/store',[KariawanController::class,'store']);
    Route::get('kariawan/show/{id}',[KariawanController::class,'show']);
    Route::put('kariawan/edit/{id}',[KariawanController::class,'edit']);
    Route::patch('kariawan/update/{id}',[KariawanController::class,'update']);
    Route::delete('kariawan/delete/{id}',[KariawanController::class,'delete']);

    // Pengiriman
    Route::get('pengiriman',[PengirimanController::class,'index']);
    Route::post('pengiriman/store', [PengirimanController::class, 'store']);
    Route::get('pengiriman/show/{id}', [PengirimanController::class, 'show']);
    Route::patch('pengiriman/update/{id}', [PengirimanController::class, 'update']);
    Route::delete('pengiriman/delete/{id}', [PengirimanController::class, 'destroy']);

    // Penjualan
    Route::get('penjualan',[PenjualanController::class,'index']);
    Route::post('penjualan/store',[PenjualanController::class,'store']);
    Route::get('penjualan/show/{id}',[PenjualanController::class,'show']);
    Route::patch('penjualan/update/{id}',[PenjualanController::class,'update']);
    Route::delete('penjualan/delete/{id}',[PenjualanController::class,'destroy']);

    // Barang Masuk
    Route::get('barang-masuk',[BarangMasukController::class,'index']);
    Route::post('barang-masuk/store',[BarangMasukController::class,'store']);
    Route::get('barang-masuk/show/{id}',[BarangMasukController::class,'show']);
    Route::patch('barang-masuk/update/{id}',[BarangMasukController::class,'update']);
    Route::delete('barang-masuk/delete/{id}',[BarangMasukController::class,'destroy']);

    // Relasi Market Produk
    Route::get('market-produk',[RelationController::class,'index']);
    Route::post('market-produk/store',[RelationController::class,'store']);

    // Stok Market
    Route::get('stok-market',[ProdukMarketController::class,'index']);
});
