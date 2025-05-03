<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\AritmatikaStockController; // import controller aritmatika

class ProdukController extends Controller
{
    public function index(){
        $product = Produk::with('supply')->get();
        return response()->json([
            'data' => $product
        ], 201);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'id_supply'=> 'required|string|max:255',
        ],[
            'id_supply.required'=>'Id supply wajib di isi !!!',
            'id_supply.integer'=>'id supply berupa angka bukan huruf !!!',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Produk::create([
            'id_supply' => $request->id_supply,
        ]);

        return response()->json([
            'data' => $product,
            'message' => 'id_supply berhasil disimpan'
        ], 201);
    }

    public function show($id){
        $zee = Produk::where('id', $id)->first();

        if (!$zee) {
            return response()->json([
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        // Ambil id_supply dan id_market dari produk
        $id_supply = $zee->id_supply;
        $id_market = $zee->id_market;

        // Hitung stok dari AritmatikaStockController
        $aritmatika = new AritmatikaStockController();
        $stokResponse = $aritmatika->JumlahStockToko($id_supply, $id_market);
        $stokData = $stokResponse->getData();

        return response()->json([
            'data' => $zee,
            'jumlah_stock_toko' => $stokData->jumlah_stock_toko ?? 0,
            'message' => 'Id supply sudah ditemukan',
        ], 201);
    }

    public function update(Request $request, Produk $id)
    {
        $validator = Validator::make($request->all(), [
            'id_supply' => 'required|string|max:255',
        ],[
            'id_supply.required'=>'kode barang wajib di isi !!!',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $id->update($request->all());

        return response()->json($id);
    }

    public function delete(Produk $id)
    {
        $id->delete();
        return response()->json(['message' => 'Data Berhasil dihapus']);
    }
}
