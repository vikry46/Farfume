<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BarangMasukController extends Controller
{
    // Menampilkan semua data Barang Masuk dengan relasi Suplly
    public function index()
    {
        $data = BarangMasuk::with('supplie')->get();
        return response()->json([
            'data' => $data
        ], 200);
    }

    // Menyimpan data baru Barang Masuk
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_supplie'      => 'required|uuid|exists:supplies,id',
            'juml_masuk'      => 'required|numeric|min:1',
            'tanggal_masuk'   => 'required|date',
        ], [
            'id_supplie.required'     => 'ID Suplie wajib diisi',
            'id_supplie.exists'       => 'ID Suplie tidak ditemukan',
            'juml_masuk.required'     => 'Jumlah masuk wajib diisi',
            'tanggal_masuk.required'  => 'Tanggal masuk wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $barangMasuk = BarangMasuk::create([
            'id_supplie'     => $request->id_supplie,
            'juml_masuk'     => $request->juml_masuk,
            'tanggal_masuk'  => $request->tanggal_masuk,
        ]);

        return response()->json([
            'data'    => $barangMasuk->load('supplie'),
            'message' => 'Data Barang Masuk berhasil disimpan'
        ], 201);
    }

   // show function
    public function show($uuid)
    {
        $data = BarangMasuk::with('supplie')->where('id', $uuid)->first();

        if (!$data) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'data'    => $data,
            'message' => 'Data berhasil ditemukan'
        ], 200);
    }

    // update function
    public function update(Request $request, $uuid)
    {
        $barangMasuk = BarangMasuk::where('id', $uuid)->first();

        if (!$barangMasuk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_supplie'      => 'required|uuid|exists:supplies,id',
            'juml_masuk'      => 'required|numeric|min:1',
            'tanggal_masuk'   => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $barangMasuk->update($request->only([
            'id_supplie',
            'juml_masuk',
            'tanggal_masuk'
        ]));

        return response()->json([
            'data'    => $barangMasuk->load('supplie'),
            'message' => 'Data berhasil diperbarui'
        ], 200);
    }

    // delete function
    public function destroy($uuid)
    {
        $barangMasuk = BarangMasuk::where('id', $uuid)->first(); 

        if (!$barangMasuk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $barangMasuk->delete();
        return response()->json(['message' => 'Data berhasil dihapus'], 200);
    }
}