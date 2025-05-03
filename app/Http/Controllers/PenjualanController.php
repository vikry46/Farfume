<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with(['Market', 'Supplie'])->get();
        return response()->json([
            'success'   => true,
            'message'   => 'List semua pengiriman',
            'data'      => $penjualan
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        $penjualan = Penjualan::with(['Market', 'Supplie'])->find($id);

        if (!$penjualan) {
            return response()->json([
                'success'   => false,
                'message'   => 'Data tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success'  => true,
            'message'   => 'Detail pengirim',
            'data'      => $penjualan
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'id_market'     => 'required|exists:markets,id',
            'id_supplie'    => 'required|exists:supplies,id',
            'terjual'       => 'required|integer|min:1',
            'ukuran_botol'  => 'required|integer|in:50,100,200',    
            'harga'         => 'required|integer|min:1',    
            'tanggal'       => 'required|date',
        ]); 

        // Hitung total sebelumnya untuk supplie yang sama
        $totalSebelumnya = Penjualan::where('id_supplie', $request->id_supplie)->sum('terjual');
        $totalSekarang = $totalSebelumnya + $request->terjual;

        $totalEstimasi = floor($totalSekarang / $request->ukuran_botol);
        $estimasiSebelumnya = floor($totalSebelumnya / $request->ukuran_botol);
        $estimasi_botol = $totalEstimasi - $estimasiSebelumnya;

        // Simpan data penjualan ke tabel penjualans
        $penjualan = Penjualan::create([
            'id'            => Str::uuid(),         
            'id_market'     => $request->id_market,
            'id_supplie'    => $request->id_supplie,
            'terjual'       => $request->terjual,
            'estimasi_botol'=> $estimasi_botol,     
            'ukuran_botol'  => $request->ukuran_botol, 
            'harga'         => $request->harga, 
            'tanggal'       => $request->tanggal,
            'delete'        => false
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Pengiriman data ditambahkan',
            'data'      => $penjualan
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        // Cari penjualan yang akan diperbarui
        $penjualan = Penjualan::find($id);
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validasi input
        $request->validate([
            'id_market'    => 'exists:markets,id',
            'id_supplie'   => 'exists:supplies,id',
            'terjual'      => 'integer|min:1',
            'harga'        => 'integer|min:1',
            'ukuran_botol' => 'integer|in:50,100,200',  
            'tanggal'      => 'date'
        ]);

        // Gunakan nilai baru, atau nilai lama jika tidak diinput
        $terjualBaru = $request->terjual ?? $penjualan->terjual;
        $ukuranBotolBaru = $request->ukuran_botol ?? $penjualan->ukuran_botol;
        $idSupplie = $request->id_supplie ?? $penjualan->id_supplie;
        $harga = $request->harga ?? $penjualan->harga;

        // Hitung total sebelumnya (kecuali data yang sedang diedit)
        $totalSebelumnya = Penjualan::where('id_supplie', $idSupplie)
            ->where('id', '!=', $penjualan->id)
            ->sum('terjual');
        $totalSekarang = $totalSebelumnya + $terjualBaru;

        $totalEstimasi = floor($totalSekarang / $ukuranBotolBaru);
        $estimasiSebelumnya = floor($totalSebelumnya / $ukuranBotolBaru);
        $estimasi_botol = $totalEstimasi - $estimasiSebelumnya;

        // Update data penjualan
        $penjualan->update([
            'id_market'      => $request->id_market ?? $penjualan->id_market,
            'id_supplie'     => $idSupplie,
            'terjual'        => $terjualBaru,
            'ukuran_botol'   => $ukuranBotolBaru,
            'estimasi_botol' => $estimasi_botol,
            'harga'          => $harga,
            'tanggal'        => $request->tanggal ?? $penjualan->tanggal,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Data berhasil diperbarui',
            'data'      => $penjualan
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        if (!$penjualan) {
            return response()->json([
                'success'  => false,
                'message'   => 'Data tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        }

        $penjualan->delete();

        return response()->json([
            'success'  => true,
            'message'   => 'Data berhasil dihapus'
        ], Response::HTTP_OK);
    }
}
