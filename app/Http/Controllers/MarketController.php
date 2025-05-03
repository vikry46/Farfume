<?php

namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Pengiriman;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MarketController extends Controller
{
    public function index(){
        $christy = Market::all();
        return response()->json([
            'data' => $christy
        ], 201);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama'=> 'required|string|max:255',
            'kode_cabang'=> 'required|string|max:255|unique:markets,kode_cabang',
        ],[
            'nama.required'=>'Id supply wajib di isi !!!',
            'nama.string'=>'id supply berupa huruf bukan angka !!!',
            'kode_cabang.required'=>'kode barang waji di isi !!!',
            'kode_cabang.unique' => 'Kode sudah digunakan, silakan gunakan Kode lain',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $adel = Market::create([
            'nama' => $request->nama,
            'kode_cabang' => $request->kode_cabang,
        ]);

        return response()->json([
            'data' => $adel,
            'message' => 'id supply berhasil di simpan'
        ], 201);
    }

    public function show($id){
        $zee = Market::where('id', $id)->first();
        return response()->json([
            'data' => $zee,
            'message' => 'Id supply sudah ditemukan',
        ], 201);
    }

    public function update(Request $request, Market $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kode_cabang' => 'required|string|max:255|unique:markets,kode_cabang,' . $id->id,
        ],[
            'nama.required'=>'Id supply wajib di isi !!!',
            'nama.string'=>'Nama berupa huruf bukan angka !!!',
            'kode_cabang.required'=>'kode barang wajib di isi !!!',
            'kode_cabang.unique' => 'Kode sudah digunakan, silakan gunakan Kode lain',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $id->update($request->all());

        return response()->json($id);
    }

    public function delete(Market $id)
    {
        $id->delete();  
        return response()->json(['message' => 'Data Berhasil di hapus']);
    }

    // Method Detail Stok Toko berdasarkan id_supplie
    public function detailStokToko($id_supplie)
    {
        $markets = Pengiriman::where('id_supplie', $id_supplie)
                    ->pluck('id_market')
                    ->unique();

        $detailPerToko = [];

        foreach ($markets as $marketId) {
            $jumlahKirim = Pengiriman::where('id_supplie', $id_supplie)
                                ->where('id_market', $marketId)
                                ->sum('jumlah_kirim');

            $jumlahTerjual = Penjualan::where('id_supplie', $id_supplie)
                                ->where('id_market', $marketId)
                                ->sum('terjual');

            $stokToko = $jumlahKirim - $jumlahTerjual;

            $namaToko = Market::find($marketId)?->nama ?? 'Tidak diketahui';

            $detailPerToko[] = [
                'id_supplie' => $id_supplie,
                'id_toko' => $marketId,
                'nama_toko' => $namaToko,
                'detail_stok_toko' => $stokToko
            ];
        }

        return response()->json([
            'success' => true,
            'rincian_stok_per_toko' => $detailPerToko
        ]);
    }
}
