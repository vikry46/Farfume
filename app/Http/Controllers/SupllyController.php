<?php

namespace App\Http\Controllers;

use App\Models\Suplly;
use App\Models\Pengiriman;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupllyController extends Controller
{
    private function hitungJumlahAll($id_supplie)
{
    // Ambil semua market terkait supplie
    $markets = Pengiriman::where('id_supplie', $id_supplie)
                ->pluck('id_market')
                ->unique();

    $totalStockMarket = 0;
    $totalKirimKeseluruhan = 0;

    foreach ($markets as $market) {
        $jumlahKirim = Pengiriman::where('id_supplie', $id_supplie)
                            ->where('id_market', $market)
                            ->sum('jumlah_kirim');

        $jumlahTerjual = Penjualan::where('id_supplie', $id_supplie)
                            ->where('id_market', $market)
                            ->sum('terjual');

        $stokToko = $jumlahKirim - $jumlahTerjual;

        // Akumulasi
        $totalStockMarket += $stokToko;
        $totalKirimKeseluruhan += $jumlahKirim;
    }

    // Total barang masuk
    $totalBarangMasuk = \App\Models\BarangMasuk::where('id_supplie', $id_supplie)->sum('juml_masuk');

    // Rumus baru: total stock = stok toko + barang masuk - total kirim
    return $totalStockMarket + $totalBarangMasuk - $totalKirimKeseluruhan;
}

    // Menampilkan semua data suplly
    public function index()
    {
        $supply = Suplly::all()->map(function ($item) {
            $item->jumlah_all = $this->hitungJumlahAll($item->id);
            return $item;
        });

        return response()->json([
            'data' => $supply
        ], 200);
    }

    // Menyimpan data suplly baru
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama'         => 'required|string|max:255',
            'kode_barang'  => 'required|string|max:255',
        ],[
            'nama.required'         => 'Nama wajib diisi',
            'nama.string'           => 'Nama harus berupa huruf',
            'kode_barang.required'  => 'Kode barang wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $suplly = Suplly::create([
            'nama'        => $request->nama,
            'kode_barang' => $request->kode_barang,
        ]);

        // Hitung jumlah_all setelah menyimpan
        $suplly->jumlah_all = $this->hitungJumlahAll($suplly->id);

        return response()->json([
            'data'    => $suplly,
            'message' => 'Suplly berhasil disimpan'
        ], 201);
    }

    // Menampilkan suplly berdasarkan ID
    public function show($id) {
        $data = Suplly::find($id);

        if (!$data) {
            return response()->json(['message' => 'Suplly tidak ditemukan'], 404);
        }

        $data->jumlah_all = $this->hitungJumlahAll($id);
        $data->totalstock = $data->jumlah_all;

        return response()->json([
            'data'    => $data,
            'message' => 'Suplly ditemukan',
        ], 200);
    }

    // Mengupdate data suplly
    public function update(Request $request, $id) {
        $suplly = Suplly::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama'        => 'required|string|max:255',
            'kode_barang' => 'required|string|max:255',

        ],);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $suplly->update($request->all());

        // Hitung jumlah_all setelah update
        $suplly->jumlah_all = $this->hitungJumlahAll($suplly->id);
        $suplly->totalstock = $suplly->jumlah_all;

        return response()->json([
            'data'    => $suplly,
            'message' => 'Suplly berhasil diperbarui'
        ], 200);
    }

    // Menghapus data suplly
    public function delete($id) {
        $suplly = Suplly::find($id);

        if (!$suplly) {
            return response()->json(['message' => 'Suplly tidak ditemukan'], 404);
        }

        $suplly->delete();
        return response()->json(null, 204);
    }
}
