<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pengiriman;
use App\Models\UkuranBotol;
use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Validasi akses tenant
        if (!$user->tenant_id && !$user->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki akses tenant',
            ], Response::HTTP_FORBIDDEN);
        }

        // Ambil daftar market yang user punya akses dalam tenant yang sama
        $marketQuery = $user->markets()->active();
        
        // Jika bukan superadmin, filter berdasarkan tenant
        if (!$user->hasRole('superadmin')) {
            $marketQuery->where('tenant_id', $user->tenant_id);
        }
        
        $marketIds = $marketQuery->pluck('markets.id')->toArray();

        if (empty($marketIds)) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada market yang dapat diakses',
                'data' => collect([]),
            ], 200);
        }

        // Query penjualan yang hanya di market yang user punya akses, dan belum dihapus
        $penjualanQuery = Penjualan::with(['market.tenant', 'supplie'])
            ->whereIn('id_market', $marketIds)
            ->active();

        // Filter tambahan berdasarkan tenant jika bukan superadmin
        if (!$user->hasRole('superadmin')) {
            $penjualanQuery->forTenant($user->tenant_id);
        }

        $penjualan = $penjualanQuery
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar penjualan yang dapat diakses user',
            'data' => $penjualan,
            'tenant_info' => $user->tenant ? [
                'tenant_id' => $user->tenant->id,
                'tenant_name' => $user->tenant->nama,
            ] : null,
        ], 200);
    }

    public function show($id)
    {
        $user = auth()->user();
        
        $penjualanQuery = Penjualan::with(['market.tenant', 'supplie']);
        
        // Filter berdasarkan tenant jika bukan superadmin
        if (!$user->hasRole('superadmin')) {
            $penjualanQuery->forTenant($user->tenant_id);
        }
        
        $penjualan = $penjualanQuery->find($id);

        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau tidak memiliki akses',
            ], Response::HTTP_NOT_FOUND);
        }

        // Validasi akses market
        if (!$user->hasAccessToMarket($penjualan->id_market)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke market ini',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail penjualan',
            'data' => $penjualan
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Validasi akses tenant
        if (!$user->tenant_id && !$user->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki akses tenant',
            ], Response::HTTP_FORBIDDEN);
        }

        $daftarUkuran = UkuranBotol::pluck('ukuran_botol')->toArray();

        $request->validate([
            'id_market' => [
                'required',
                'exists:markets,id',
                function ($attribute, $value, $fail) use ($user) {
                    // Validasi market milik tenant yang sama
                    $market = Market::find($value);
                    if (!$market || (!$user->hasRole('superadmin') && $market->tenant_id !== $user->tenant_id)) {
                        $fail('Market tidak ditemukan atau tidak memiliki akses.');
                    }
                    
                    // Validasi user memiliki akses ke market
                    if (!$user->hasAccessToMarket($value)) {
                        $fail('Tidak memiliki akses ke market ini.');
                    }
                }
            ],
            'id_supplie' => 'required|exists:supplies,id',
            'terjual' => 'required|integer|min:1',
            'ukuran_botol' => ['required', 'integer', Rule::in($daftarUkuran)],
            'harga' => 'required|integer|min:1',
            'tanggal' => 'required|date',
        ]);

        // Perhitungan stok hanya untuk suplai dan market yang sama
        $totalBotolMasuk = Pengiriman::where('id_supplie', $request->id_supplie)
            ->where('id_market', $request->id_market)
            ->sum('jumlah_kirim');

        $stokTersediaML = $totalBotolMasuk * $request->ukuran_botol;

        $totalTerjualSebelumnya = Penjualan::where('id_supplie', $request->id_supplie)
            ->where('id_market', $request->id_market)
            ->active()
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
            'id' => Str::uuid(),
            'id_market' => $request->id_market,
            'id_supplie' => $request->id_supplie,
            'terjual' => $request->terjual,
            'estimasi_botol' => $estimasi_botol,
            'ukuran_botol' => $request->ukuran_botol,
            'harga' => $request->harga,
            'tanggal' => $request->tanggal,
            'delete' => false
        ]);

        // Load relasi untuk response
        $penjualan->load(['market.tenant', 'supplie']);

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan berhasil ditambahkan',
            'data' => $penjualan
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        // Cari penjualan dengan validasi tenant
        $penjualanQuery = Penjualan::query();
        
        if (!$user->hasRole('superadmin')) {
            $penjualanQuery->forTenant($user->tenant_id);
        }
        
        $penjualan = $penjualanQuery->find($id);
        
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau tidak memiliki akses'
            ], Response::HTTP_NOT_FOUND);
        }

        // Validasi akses market
        if (!$user->hasAccessToMarket($penjualan->id_market)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke market ini',
            ], Response::HTTP_FORBIDDEN);
        }

        $daftarUkuran = UkuranBotol::pluck('ukuran_botol')->toArray();

        $request->validate([
            'id_market' => [
                'exists:markets,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        $market = Market::find($value);
                        if (!$market || (!$user->hasRole('superadmin') && $market->tenant_id !== $user->tenant_id)) {
                            $fail('Market tidak ditemukan atau tidak memiliki akses.');
                        }
                        
                        if (!$user->hasAccessToMarket($value)) {
                            $fail('Tidak memiliki akses ke market ini.');
                        }
                    }
                }
            ],
            'id_supplie' => 'exists:supplies,id',
            'terjual' => 'integer|min:1',
            'harga' => 'integer|min:1',
            'ukuran_botol' => ['integer', Rule::in($daftarUkuran)],
            'tanggal' => 'date'
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
            ->active()
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
            'id_market' => $idMarketBaru,
            'id_supplie' => $idSupplieBaru,
            'terjual' => $terjualBaru,
            'ukuran_botol' => $ukuranBotolBaru,
            'estimasi_botol' => $estimasi_botol,
            'harga' => $hargaBaru,
            'tanggal' => $request->tanggal ?? $penjualan->tanggal,
        ]);

        // Load relasi untuk response
        $penjualan->load(['market.tenant', 'supplie']);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $penjualan
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        
        // Cari penjualan dengan validasi tenant
        $penjualanQuery = Penjualan::query();
        
        if (!$user->hasRole('superadmin')) {
            $penjualanQuery->forTenant($user->tenant_id);
        }
        
        $penjualan = $penjualanQuery->find($id);
        
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau tidak memiliki akses',
            ], Response::HTTP_NOT_FOUND);
        }

        // Validasi akses market
        if (!$user->hasAccessToMarket($penjualan->id_market)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke market ini',
            ], Response::HTTP_FORBIDDEN);
        }

        $penjualan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus'
        ], Response::HTTP_OK);
    }

    // Function grafik dengan tenant isolation
    public function grafikTrenPenjualan()
    {
        $user = auth()->user();
        
        $query = DB::table('penjualans')
            ->join('markets', 'penjualans.id_market', '=', 'markets.id')
            ->selectRaw('DATE(penjualans.tanggal) as tanggal, SUM(penjualans.terjual) as total_terjual')
            ->where('penjualans.delete', false);

        // Filter berdasarkan tenant jika bukan superadmin
        if (!$user->hasRole('superadmin')) {
            $query->where('markets.tenant_id', $user->tenant_id);
        }

        // Filter berdasarkan market yang bisa diakses user
        $marketIds = $user->getAccessibleMarkets()->pluck('id')->toArray();
        if (!empty($marketIds)) {
            $query->whereIn('penjualans.id_market', $marketIds);
        }

        $result = $query->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $result,
            'tenant_info' => $user->tenant ? [
                'tenant_id' => $user->tenant->id,
                'tenant_name' => $user->tenant->nama,
            ] : null,
        ]);
    }

    public function grafikPerProduk()
    {
        $user = auth()->user();
        
        $query = DB::table('penjualans')
            ->join('supplies', 'penjualans.id_supplie', '=', 'supplies.id')
            ->join('markets', 'penjualans.id_market', '=', 'markets.id')
            ->selectRaw('supplies.nama as produk, SUM(penjualans.terjual) as total_terjual')
            ->where('penjualans.delete', false);

        // Filter berdasarkan tenant jika bukan superadmin
        if (!$user->hasRole('superadmin')) {
            $query->where('markets.tenant_id', $user->tenant_id);
        }

        // Filter berdasarkan market yang bisa diakses user
        $marketIds = $user->getAccessibleMarkets()->pluck('id')->toArray();
        if (!empty($marketIds)) {
            $query->whereIn('penjualans.id_market', $marketIds);
        }

        $result = $query->groupBy('produk')->get();

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function grafikPerMarket()
    {
        $user = auth()->user();
        
        $query = DB::table('penjualans')
            ->join('markets', 'penjualans.id_market', '=', 'markets.id')
            ->selectRaw('markets.nama as market, SUM(penjualans.terjual) as total_terjual')
            ->where('penjualans.delete', false);

        // Filter berdasarkan tenant jika bukan superadmin
        if (!$user->hasRole('superadmin')) {
            $query->where('markets.tenant_id', $user->tenant_id);
        }

        // Filter berdasarkan market yang bisa diakses user
        $marketIds = $user->getAccessibleMarkets()->pluck('id')->toArray();
        if (!empty($marketIds)) {
            $query->whereIn('penjualans.id_market', $marketIds);
        }

        $result = $query->groupBy('market')->get();

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function grafikRevenue()
    {
        $user = auth()->user();
        
        $query = DB::table('penjualans')
            ->join('markets', 'penjualans.id_market', '=', 'markets.id')
            ->selectRaw('DATE(penjualans.tanggal) as tanggal, SUM(penjualans.terjual * penjualans.harga) as revenue')
            ->where('penjualans.delete', false);

        // Filter berdasarkan tenant jika bukan superadmin
        if (!$user->hasRole('superadmin')) {
            $query->where('markets.tenant_id', $user->tenant_id);
        }

        // Filter berdasarkan market yang bisa diakses user
        $marketIds = $user->getAccessibleMarkets()->pluck('id')->toArray();
        if (!empty($marketIds)) {
            $query->whereIn('penjualans.id_market', $marketIds);
        }

        $result = $query->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function grafikPerUkuran()
    {
        $user = auth()->user();
        
        $query = DB::table('penjualans')
            ->join('markets', 'penjualans.id_market', '=', 'markets.id')
            ->selectRaw('penjualans.ukuran_botol, SUM(penjualans.terjual) as total_terjual')
            ->where('penjualans.delete', false);

        // Filter berdasarkan tenant jika bukan superadmin
        if (!$user->hasRole('superadmin')) {
            $query->where('markets.tenant_id', $user->tenant_id);
        }

        // Filter berdasarkan market yang bisa diakses user
        $marketIds = $user->getAccessibleMarkets()->pluck('id')->toArray();
        if (!empty($marketIds)) {
            $query->whereIn('penjualans.id_market', $marketIds);
        }

        $result = $query->groupBy('penjualans.ukuran_botol')
                        ->orderBy('penjualans.ukuran_botol')
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}