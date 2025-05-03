<?php

use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\KariawanController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProdukMarketController;
use App\Http\Controllers\RelationController;
use App\Http\Controllers\SupllyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});

Route::get('supllies', [SupllyController::class, 'index'])->name('suplly.index');
Route::post('suplly/store', [SupllyController::class, 'store'])->name('suplly.store');
Route::get('suplly/show/{id}', [SupllyController::class, 'show'])->name('suplly.show');
Route::put('suplly/edit/{id}', [SupllyController::class, 'edit'])->name('suplly.edit');
Route::patch('suplly/update/{id}', [SupllyController::class, 'update'])->name('suplly.update');
Route::delete('suplly/delete/{id}', [SupllyController::class, 'delete'])->name('suplly.delete');

Route::get('Product', [ProdukController::class,'index'])->name('product.index');
Route::post('Product/store', [ProdukController::class,'store'])->name('product.store');
Route::get('Product/show/{id}', [ProdukController::class,'show'])->name('product.show');
Route::put('Product/edit/{id}', [ProdukController::class,'edit'])->name('product.edit');
Route::patch('Product/update/{id}', [ProdukController::class,'update'])->name('product.update');
Route::delete('Product/delete/{id}', [ProdukController::class,'delete'])->name('product.delete');

Route::get('market', [MarketController::class,'index'])->name('market.index');
Route::post('market/store', [MarketController::class,'store'])->name('market.store');
Route::get('market/show/{id}', [MarketController::class,'show'])->name('market.show');
Route::put('market/edit/{id}', [MarketController::class,'edit'])->name('market.edit');
Route::patch('market/update/{id}', [MarketController::class,'update'])->name('market.update');
Route::delete('market/delete/{id}', [MarketController::class,'delete'])->name('market.delete');

Route::get('kariawan',[KariawanController::class,'index'])->name('kariawan.index');
Route::post('kariawan/store',[KariawanController::class,'store'])->name('kariawan.store');
Route::get('kariawan/show/{id}',[KariawanController::class,'show'])->name('kariawan.show');
Route::put('kariawan/edit/{id}',[KariawanController::class,'edit'])->name('kariawan.edit');
Route::patch('kariawan/update/{id}',[KariawanController::class,'update'])->name('kariawan.update');
Route::delete('kariawan/delete/{id}',[KariawanController::class,'delete'])->name('kariawan.delete');

Route::get('pengiriman',[PengirimanController::class,'index']);
Route::post('pengiriman/store', [PengirimanController::class, 'store']);
Route::get('pengiriman/show/{id}', [PengirimanController::class, 'show']); 
Route::patch('pengiriman/update/{id}', [PengirimanController::class, 'update']); 
Route::delete('pengiriman/delete/{id}', [PengirimanController::class, 'destroy']);

Route::get('penjualan',[PenjualanController::class,'index']);
Route::post('penjualan/store',[PenjualanController::class,'store']);
Route::get('penjualan/show/{id}',[PenjualanController::class,'show']);
Route::patch('penjualan/update/{id}',[PenjualanController::class,'update']);
Route::delete('penjualan/delete/{id}',[PenjualanController::class,'destroy']);

Route::get('barang-masuk',[BarangMasukController::class,'index']);
Route::post('barang-masuk/store',[BarangMasukController::class,'store']);
Route::get('barang-masuk/show/{id}',[BarangMasukController::class,'show']);
Route::patch('barang-masuk/update/{id}',[BarangMasukController::class,'update']);
Route::delete('barang-masuk/delete/{id}',[BarangMasukController::class,'destroy']);

Route::get('market-produk',[RelationController::class,'index']);
Route::post('market-produk/store',[RelationController::class,'store'])->name('markerProduk.store');

Route::get('stok-market',[ProdukMarketController::class,'index']);

