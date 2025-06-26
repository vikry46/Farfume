<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\Pengiriman;
use App\Models\BarangMasuk;

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
            'data'    => [
                'id' => $pengiriman->id,
                'id_market' => $pengiriman->id_market,
                'id_supplie' => $pengiriman->id_supplie,
                'jumlah_kirim' => $pengiriman->jumlah_kirim,
                'tanggal' => $pengiriman->tanggal,
                'market' => $pengiriman->market ? [
                    'id' => $pengiriman->market->id,
                    'nama' => $pengiriman->market->nama
                ] : null,
                'supplie' => $pengiriman->supplie ? [
                    'id' => $pengiriman->supplie->id,
                    'nama' => $pengiriman->supplie->nama
                ] : null
            ]
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

        // Hitung total barang masuk & pengiriman sebelumnya
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

        // Total masuk & pengiriman lain (kecuali data ini sendiri)
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

        // Update data
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
}
