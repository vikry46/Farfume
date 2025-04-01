<?php

namespace App\Http\Controllers;

use App\Models\Suplly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupllyController extends Controller
{
    // Menampilkan semua data suplly
    public function index()
    {
        $supply = Suplly::all();
        return response()->json([
            'data' => $supply
        ], 200);
    }

    // Menyimpan data suplly baru
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'nama'         => 'required|string|max:255',
            'kode_barang'  => 'required|string|max:255',
            'total_masuk'  => 'required|integer',
            'tanggal'      => 'required|date',
        ],[
            'nama.required'         => 'Nama wajib diisi',
            'nama.string'           => 'Nama harus berupa huruf',
            'kode_barang.required'  => 'Kode barang wajib diisi',
            'total_masuk.required'  => 'Jumlah keseluruhan wajib diisi',
            'total_masuk.integer'   => 'Jumlah keseluruhan harus angka',
            'tanggal.required'      => 'Tanggal wajib diisi'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $suplly = Suplly::create([
            'nama'        => $request->nama,
            'kode_barang' => $request->kode_barang,
            'total_masuk' => $request->total_masuk,
            'tanggal'     => $request->tanggal
        ]);

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
            'total_masuk' => 'required|integer',
            'tanggal'     => 'required|date',
        ],[
            'total_masuk.integer' => 'Jumlah keseluruhan harus angka'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $suplly->update($request->all());

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
