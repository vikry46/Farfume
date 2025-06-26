<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pengiriman;
use App\Models\UkuranBotol;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualan = Penjualan::with(['Market', 'Supplie'])->get();
        return response()->json([
            'success' => true,
            'message' => 'List semua pengiriman',
            'data'    => $penjualan
        ], Response::HTTP_OK);
    }

    public function show($id)
    {
        $penjualan = Penjualan::with(['Market', 'Supplie'])->find($id);

        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pengirim',
            'data'    => $penjualan
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $daftarUkuran = UkuranBotol::pluck('ukuran_botol')->toArray();

        $request->validate([
            'id_market'     => 'required|exists:markets,id',
            'id_supplie'    => 'required|exists:supplies,id',
            'terjual'       => 'required|integer|min:1',
            'ukuran_botol'  => ['required', 'integer', Rule::in($daftarUkuran)],
            'harga'         => 'required|integer|min:1',
            'tanggal'       => 'required|date',
        ]);

        // Perhitungan stok hanya untuk suplai dan market yang sama
        $totalBotolMasuk = Pengiriman::where('id_supplie', $request->id_supplie)
            ->where('id_market', $request->id_market)
            ->sum('jumlah_kirim');

        $stokTersediaML = $totalBotolMasuk * $request->ukuran_botol;

        $totalTerjualSebelumnya = Penjualan::where('id_supplie', $request->id_supplie)
            ->where('id_market', $request->id_market)
            ->sum('terjual');

        $totalSetelahJual = $totalTerjualSebelumnya + $request->terjual;

        if ($totalSetelahJual > $stokTersediaML) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi di market ini. Sisa hanya ' . ($stokTersediaML - $totalTerjualSebelumnya) . ' ml.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $totalEstimasi = floor($totalSetelahJual / $request->ukuran_botol);
        $estimasiSebelumnya = floor($totalTerjualSebelumnya / $request->ukuran_botol);
        $estimasi_botol = $totalEstimasi - $estimasiSebelumnya;

        $penjualan = Penjualan::create([
            'id'             => Str::uuid(),
            'id_market'      => $request->id_market,
            'id_supplie'     => $request->id_supplie,
            'terjual'        => $request->terjual,
            'estimasi_botol' => $estimasi_botol,
            'ukuran_botol'   => $request->ukuran_botol,
            'harga'          => $request->harga,
            'tanggal'        => $request->tanggal,
            'delete'         => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan berhasil ditambahkan',
            'data'    => $penjualan
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $penjualan = Penjualan::find($id);
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'id_market'    => 'exists:markets,id',
            'id_supplie'   => 'exists:supplies,id',
            'terjual'      => 'integer|min:1',
            'harga'        => 'integer|min:1',
            'ukuran_botol' => 'integer|in:50,100,200',
            'tanggal'      => 'date'
        ]);

        $idMarketBaru = $request->id_market ?? $penjualan->id_market;
        $idSupplieBaru = $request->id_supplie ?? $penjualan->id_supplie;
        $terjualBaru = $request->terjual ?? $penjualan->terjual;
        $ukuranBotolBaru = $request->ukuran_botol ?? $penjualan->ukuran_botol;
        $hargaBaru = $request->harga ?? $penjualan->harga;

        $totalBotolMasuk = Pengiriman::where('id_supplie', $idSupplieBaru)
            ->where('id_market', $idMarketBaru)
            ->sum('jumlah_kirim');

        $stokTersediaML = $totalBotolMasuk * $ukuranBotolBaru;

        $totalTerjualSebelumnya = Penjualan::where('id_supplie', $idSupplieBaru)
            ->where('id_market', $idMarketBaru)
            ->where('id', '!=', $penjualan->id)
            ->sum('terjual');

        $totalSetelahJual = $totalTerjualSebelumnya + $terjualBaru;

        if ($totalSetelahJual > $stokTersediaML) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi di market ini. Sisa hanya ' . ($stokTersediaML - $totalTerjualSebelumnya) . ' ml.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $totalEstimasi = floor($totalSetelahJual / $ukuranBotolBaru);
        $estimasiSebelumnya = floor($totalTerjualSebelumnya / $ukuranBotolBaru);
        $estimasi_botol = $totalEstimasi - $estimasiSebelumnya;

        $penjualan->update([
            'id_market'      => $idMarketBaru,
            'id_supplie'     => $idSupplieBaru,
            'terjual'        => $terjualBaru,
            'ukuran_botol'   => $ukuranBotolBaru,
            'estimasi_botol' => $estimasi_botol,
            'harga'          => $hargaBaru,
            'tanggal'        => $request->tanggal ?? $penjualan->tanggal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data'    => $penjualan
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        }

        $penjualan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ], Response::HTTP_OK);
    }
}
