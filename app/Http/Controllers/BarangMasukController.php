<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Str;

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
            'items' => 'required|array|min:1',

            'items.*.id_supplie' => 'required|uuid|exists:supplies,id',

            'items.*.juml_masuk' => 'required|numeric|min:1',

            'items.*.tanggal_masuk' => 'required|date',
        ], [
            'items.required' => 'Data barang masuk wajib diisi.',
            'items.array' => 'Format data tidak valid.',

            'items.*.id_supplie.required' => 'Supplie wajib dipilih.',
            'items.*.id_supplie.exists' => 'Supplie tidak ditemukan.',

            'items.*.juml_masuk.required' => 'Jumlah masuk wajib diisi.',
            'items.*.juml_masuk.numeric' => 'Jumlah masuk harus berupa angka.',
            'items.*.juml_masuk.min' => 'Jumlah masuk minimal 1.',

            'items.*.tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'items.*.tanggal_masuk.date' => 'Format tanggal tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {

            $barangMasukList = [];

            foreach ($request->items as $item) {

                $barangMasuk = BarangMasuk::create([
                    'id_supplie' => $item['id_supplie'],
                    'juml_masuk' => $item['juml_masuk'],
                    'tanggal_masuk' => $item['tanggal_masuk'],
                ]);

                $barangMasukList[] = $barangMasuk->load('supplie');
            }

            DB::commit();

            return response()->json([
                'message' => 'Semua data berhasil disimpan.',
                'data' => $barangMasukList
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Gagal menyimpan data.',
                'error' => $e->getMessage()
            ], 500);

        }
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