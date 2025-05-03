<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Market;
use App\Models\Suplly;
use App\Models\Pengiriman;
use App\Models\Penjualan;

class ProdukMarketController extends Controller
{
    public function index(Request $request)
    {
        $stokToko = [];

        $tokos = Market::all();
        $suplais = Suplly::all(); 

        foreach ($tokos as $toko) {
            foreach ($suplais as $suplai) {
                $jumlahKirim = Pengiriman::where('id_market', $toko->id)
                                ->where('id_supplie', $suplai->id)
                                ->sum('jumlah_kirim');

                $jumlahTerjual = Penjualan::where('id_market', $toko->id)
                                ->where('id_supplie', $suplai->id)
                                ->sum('estimasi_botol');

                $stokToko[] = [
                    'toko' => $toko->nama,
                    'barang' => $suplai->nama,
                    'stok' => $jumlahKirim - $jumlahTerjual
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $stokToko
        ]);
    }
}
