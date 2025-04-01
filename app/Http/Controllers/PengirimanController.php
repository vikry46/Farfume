<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Pengiriman;
use Illuminate\Http\Response;
use Illuminate\Support\Str;


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
                'message' => 'Data tidak ditemukan'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pengiriman',
            'data'    => $pengiriman
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'id_market'    => 'required|exists:markets,id',
            'id_supplie'   => 'required|exists:supplies,id',
            'jumlah_kirim' => 'required|integer|min:1',
            'tanggal'      => 'required|date',
        ]);

        // Simpan data
        $pengiriman = Pengiriman::create([
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

        // Validasi input
        $request->validate([
            'id_market'    => 'exists:markets,id',  
            'id_supplie'   => 'exists:supplies,id',
            'jumlah_kirim' => 'integer|min:1',
            'tanggal'      => 'date', 
        ]);

        // Update data
        $pengiriman->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pengiriman berhasil diperbarui',
            'data'    => $pengiriman
        ], Response::HTTP_OK);
    }

    /**
     * Menghapus data pengiriman
     */
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
