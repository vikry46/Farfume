<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\Pengiriman;
use App\Models\BarangMasuk;
use Illuminate\Support\Facades\DB;

class PengirimanController extends Controller
{
    public function index()
    {
        $pengiriman = Pengiriman::with(['market', 'supplie'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List semua pengiriman',
            'data'    => $pengiriman
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        $pengiriman = Pengiriman::with(['market', 'supplie'])->find($id);

        if (!$pengiriman) {
            return response()->json([
                'success' => false,
                'message' => 'Pengiriman tidak ditemukan'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pengiriman ditemukan',
            'data'    => $pengiriman
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_market'    => 'required|exists:markets,id',
            'id_supplie'   => 'required|exists:supplies,id',
            'jumlah_kirim' => 'required|integer|min:1',
            'tanggal'      => 'required|date',
        ]);

        // Hitung stok
        $totalMasuk = BarangMasuk::where('id_supplie', $request->id_supplie)->sum('juml_masuk');
        $totalKirim = Pengiriman::where('id_supplie', $request->id_supplie)->sum('jumlah_kirim');
        $sisaStok = $totalMasuk - $totalKirim;

        if ($request->jumlah_kirim > $sisaStok) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pengiriman melebihi stok gudang. Sisa stok hanya ' . $sisaStok,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pengiriman = Pengiriman::create([
            'id'           => (string) Str::uuid(),
            'id_market'    => $request->id_market,
            'id_supplie'   => $request->id_supplie,
            'jumlah_kirim' => $request->jumlah_kirim,
            'tanggal'      => $request->tanggal,
            'delete'       => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengiriman berhasil ditambahkan',
            'data'    => $pengiriman    
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'jumlah_kirim' => 'required|integer|min:1',
            'tanggal'      => 'required|date',
        ]);

        $idSupplie = $request->id_supplie ?? $pengiriman->id_supplie;
        $totalMasuk = BarangMasuk::where('id_supplie', $idSupplie)->sum('juml_masuk');
        $totalKirimLain = Pengiriman::where('id_supplie', $idSupplie)
            ->where('id', '!=', $pengiriman->id)
            ->sum('jumlah_kirim');

        $sisaStok = $totalMasuk - $totalKirimLain;

        if ($request->jumlah_kirim > $sisaStok) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pengiriman melebihi stok gudang. Sisa stok hanya ' . $sisaStok,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pengiriman->update([
            'jumlah_kirim' => $request->jumlah_kirim,
            'tanggal'      => $request->tanggal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengiriman berhasil diperbarui',
            'data'    => $pengiriman
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $pengiriman = Pengiriman::find($id);

        if (!$pengiriman) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], Response::HTTP_NOT_FOUND);
        }

        $pengiriman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengiriman berhasil dihapus'
        ], Response::HTTP_OK);
    }

    // Grafik sederhana seperti PenjualanController

    public function grafikPengirimanPerBulan()
    {
        $data = DB::table('pengiriman')
            ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan, SUM(jumlah_kirim) as total_kirim')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return response()->json($data, Response::HTTP_OK);
    }

    public function grafikPengirimanPerMarket()
    {
        $data = DB::table('pengiriman')
            ->join('markets', 'pengiriman.id_market', '=', 'markets.id')
            ->selectRaw('markets.nama as market, SUM(pengiriman.jumlah_kirim) as total_kirim')
            ->groupBy('market')
            ->orderBy('total_kirim', 'desc')
            ->get();

        return response()->json($data, Response::HTTP_OK);
    }

    public function grafikPengirimanPerSupplier()
    {
        $data = DB::table('pengiriman')
            ->join('supplies', 'pengiriman.id_supplie', '=', 'supplies.id')
            ->selectRaw('supplies.nama as supplier, SUM(pengiriman.jumlah_kirim) as total_kirim')
            ->groupBy('supplier')
            ->orderBy('total_kirim', 'desc')
            ->get();

        return response()->json($data, Response::HTTP_OK);
    }

    public function grafikFrekuensiPengiriman()
    {
        $data = DB::table('pengiriman')
            ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan, COUNT(*) as jumlah_pengiriman')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return response()->json($data, Response::HTTP_OK);
    }

    public function grafikStokPerSupplier()
    {
        $data = DB::table('barang_masuks')
            ->join('supplies', 'barang_masuks.id_supplie', '=', 'supplies.id')
            ->selectRaw('supplies.nama as supplier,
                SUM(barang_masuks.juml_masuk) as total_masuk,
                (SELECT COALESCE(SUM(jumlah_kirim), 0) FROM pengiriman WHERE pengiriman.id_supplie = barang_masuks.id_supplie) as total_kirim'
            )
            ->groupBy('barang_masuks.id_supplie', 'supplies.nama')
            ->get()
            ->map(function($item) {
                $item->sisa = $item->total_masuk - $item->total_kirim;
                return $item;
            });

        return response()->json($data, Response::HTTP_OK);
    }
}
