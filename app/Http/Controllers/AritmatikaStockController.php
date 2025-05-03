<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use App\Models\Penjualan;
use App\Models\Suplly;
use Illuminate\Http\Request;

class AritmatikaStockController extends Controller
{
    
    public function JumlahStockToko($id_supplie, $id_market)
    {
        $jumlahKirim = Pengiriman::where('id_supplie', $id_supplie)
                            ->where('id_market', $id_market)
                            ->sum('jumlah_kirim');

        $jumlahTerjual = Penjualan::where('id_supplie', $id_supplie)
                            ->where('id_market', $id_market)
                            ->sum('terjual');

        $jumlahStokToko = $jumlahKirim - $jumlahTerjual;

        return response()->json([
            'success' => true,
            'jumlah_stock_toko' => $jumlahStokToko
        ]);
    }

    
    public function JumlahStockKeseluruhan($id_supplie)
    {
        $markets = Pengiriman::where('id_supplie', $id_supplie)
                    ->pluck('id_market')
                    ->unique();

        $totalStock = 0;
        $detailPerToko = [];

        foreach ($markets as $market) {
            $jumlahKirim = Pengiriman::where('id_supplie', $id_supplie)
                                ->where('id_market', $market)
                                ->sum('jumlah_kirim');

            $jumlahTerjual = Penjualan::where('id_supplie', $id_supplie)
                                ->where('id_market', $market)
                                ->sum('terjual');

            $stokToko = $jumlahKirim - $jumlahTerjual;
            $totalStock += $stokToko;

            $detailPerToko[] = [
                'id_market' => $market,
                'jumlah_pertoko' => $stokToko
            ];
        }

        $totalMasuk = Suplly::where('id', $id_supplie)->sum('jumlah_masuk');

        $totalAll = $totalStock + $totalMasuk;

        return response()->json([
            'success' => true,
            'jumlah_all' => $totalAll,
            'rincian_per_toko' => $detailPerToko
        ]);
    }
}
