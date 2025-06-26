<?php

namespace App\Http\Controllers;

use App\Models\UkuranBotol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UkuranBotolController extends Controller
{
    // GET: Ambil semua data ukuran botol
    public function index()
    {
        $data = UkuranBotol::all();
        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    // POST: Simpan data baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ukuran_botol' => 'required|numeric|min:1',
        ], [
            'ukuran_botol.required' => 'Ukuran botol wajib diisi!',
            'ukuran_botol.string' => 'Ukuran botol harus berupa teks!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ukuran = UkuranBotol::create([
            'ukuran_botol' => $request->ukuran_botol,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ukuran botol berhasil ditambahkan.',
            'data' => $ukuran
        ], 201);
    }

    // GET: Ambil detail ukuran botol berdasarkan ID
    public function show($id)
    {
        $ukuran = UkuranBotol::find($id);

        if (!$ukuran) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ukuran
        ], 200);
    }

    // PUT/PATCH: Update ukuran botol
    public function update(Request $request, $id)
    {
        $ukuran = UkuranBotol::find($id);

        if (!$ukuran) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'ukuran_botol' => 'required|numeric|min:1',
        ], [
            'ukuran_botol.required' => 'Ukuran botol wajib diisi!',
            'ukuran_botol.string' => 'Ukuran botol harus berupa angka!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ukuran->update([
            'ukuran_botol' => $request->ukuran_botol
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ukuran botol berhasil diperbarui.',
            'data' => $ukuran
        ], 200);
    }

    // DELETE: Hapus ukuran botol
    public function destroy($id)
    {
        $ukuran = UkuranBotol::find($id);

        if (!$ukuran) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        $ukuran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ukuran botol berhasil dihapus.'
        ], 200);
    }
}
