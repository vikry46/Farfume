<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Pengiriman;
use App\Models\Suplly;
use Illuminate\Http\Response;

class GudangController extends Controller
{
    public function index()
    {
        // Ambil semua supplie
        $supplies = Suplly::all();

        // Hitung stok gudang per supplie
        $data = $supplies->map(function ($supplie) {
            $totalMasuk = BarangMasuk::where('id_supplie', $supplie->id)->sum('juml_masuk');
            $totalKirim = Pengiriman::where('id_supplie', $supplie->id)->sum('jumlah_kirim');
            $stokGudang = $totalMasuk - $totalKirim;

            return [
                'id_supplie' => $supplie->id,
                'nama_supplie' => $supplie->nama,
                'total_masuk' => $totalMasuk,
                'total_kirim' => $totalKirim,
                'stok_gudang' => $stokGudang,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data stok gudang per supplie',
            'data'    => $data
        ], Response::HTTP_OK);
    }
}
