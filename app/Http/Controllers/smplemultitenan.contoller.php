<?php

// 1. MIGRATION: Tambah tabel user_markets untuk relasi many-to-many
// php artisan make:migration create_user_markets_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMarketsTable extends Migration
{
    public function up()
    {
        Schema::create('user_markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('market_id'); // UUID dari markets table
            $table->foreign('market_id')->references('id')->on('markets')->onDelete('cascade');
            $table->enum('market_role', ['admin', 'kasir', 'supervisor'])->default('kasir');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'market_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['market_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_markets');
    }
}

// 2. MIGRATION: Tambah kolom user_id ke table penjualan untuk tracking
// php artisan make:migration add_user_id_to_penjualan_table

class AddUserIdToPenjualanTable extends Migration
{
    public function up()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('tanggal_jual')->constrained();
            $table->index(['id_market', 'tanggal_jual']);
        });
    }

    public function down()
    {
        Schema::table('penjualan', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}

// 3. UPDATE Model User.php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $appends = ['role'];
    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRoleAttribute()
    {
        return $this->getRoleNames()->first();
    }

    // Relasi many-to-many dengan markets
    public function markets(): BelongsToMany
    {
        return $this->belongsToMany(Market::class, 'user_markets', 'user_id', 'market_id')
                    ->withPivot('market_role', 'is_active')
                    ->wherePivot('is_active', true)
                    ->withTimestamps();
    }

    // Check apakah user adalah superadmin
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    // Check apakah user punya akses ke market tertentu
    public function hasAccessToMarket($marketId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return $this->markets()->where('market_id', $marketId)->exists();
    }

    // Get role user di market tertentu
    public function getRoleInMarket($marketId): ?string
    {
        if ($this->isSuperAdmin()) {
            return 'superadmin';
        }

        $market = $this->markets()->where('market_id', $marketId)->first();
        return $market ? $market->pivot->market_role : null;
    }

    // Get semua market yang bisa diakses user
    public function getAccessibleMarkets()
    {
        if ($this->isSuperAdmin()) {
            return Market::all();
        }
        
        return $this->markets;
    }

    // Check permission dengan scope market
    public function canAccessMarketResource($permission, $marketId = null): bool
    {
        // Check global permission dulu
        if (!$this->can($permission)) {
            return false;
        }

        // Jika superadmin, bisa akses semua
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Jika ada marketId, check akses ke market tersebut
        if ($marketId) {
            return $this->hasAccessToMarket($marketId);
        }

        return true;
    }
}

// 4. UPDATE Model Market.php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Market extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'nama',
        'kode_cabang',
        'delete'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($market) {
            $market->id = (string) Str::uuid();
        });
    }

    // Relasi many-to-many dengan users
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_markets', 'market_id', 'user_id')
                    ->withPivot('market_role', 'is_active')
                    ->wherePivot('is_active', true)
                    ->withTimestamps();
    }

    // Check apakah user punya akses ke market ini
    public function userHasAccess($userId): bool
    {
        $user = User::find($userId);
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) return true;
        
        return $this->users()->where('user_id', $userId)->exists();
    }

    // Existing relationships
    public function marketProduk()
    {
        return $this->hasMany(MarketProdukRelation::class);
    }

    public function marketPengiriman()
    {
        return $this->hasMany(Pengiriman::class);
    }

    public function marketPenjualan()
    {
        return $this->hasMany(Penjualan::class);
    }
}

// 5. MIDDLEWARE: CheckMarketAccess
// php artisan make:middleware CheckMarketAccess

<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Market;
use Illuminate\Support\Facades\Auth;

class CheckMarketAccess
{
    public function handle(Request $request, Closure $next, $requiredRole = null)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Superadmin bisa akses semua
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Get market ID dari route parameter atau request
        $marketId = $request->route('market') ?? 
                   $request->route('id') ?? 
                   $request->input('market_id') ?? 
                   $request->input('id_market');
        
        if ($marketId) {
            $market = $marketId instanceof Market ? $marketId : Market::find($marketId);
            
            if (!$market || !$market->userHasAccess($user->id)) {
                return response()->json([
                    'message' => 'Tidak memiliki akses ke market ini'
                ], 403);
            }

            // Check role dalam market jika diperlukan
            if ($requiredRole) {
                $userRoleInMarket = $user->getRoleInMarket($market->id);
                
                $roleHierarchy = [
                    'kasir' => ['kasir'],
                    'supervisor' => ['kasir', 'supervisor'],
                    'admin' => ['kasir', 'supervisor', 'admin'],
                ];

                if (!in_array($userRoleInMarket, $roleHierarchy[$requiredRole] ?? [])) {
                    return response()->json([
                        'message' => 'Tidak memiliki role yang diperlukan untuk operasi ini'
                    ], 403);
                }
            }

            // Simpan market ke request untuk digunakan di controller
            $request->merge(['current_market' => $market]);
        }

        return $next($request);
    }
}

// 6. UPDATE MarketController.php
<?php
namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\Pengiriman;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketController extends Controller
{
    public function __construct()
    {
        // Apply permission middleware
        $this->middleware('permission:index-markets')->only(['index']);
        $this->middleware('permission:create-markets')->only(['store']);
        $this->middleware('permission:show-markets')->only(['show']);
        $this->middleware('permission:update-markets')->only(['update']);
        $this->middleware('permission:delete-markets')->only(['delete']);
    }

    public function index()
    {
        $user = Auth::user();
        
        // Filter berdasarkan akses user
        $markets = $user->getAccessibleMarkets();
        
        return response()->json([
            'data' => $markets,
            'user_role' => $user->role,
            'accessible_markets_count' => $markets->count()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kode_cabang' => 'required|string|max:255|unique:markets,kode_cabang',
        ], [
            'nama.required' => 'Nama market wajib diisi',
            'nama.string' => 'Nama berupa huruf bukan angka',
            'kode_cabang.required' => 'Kode cabang wajib diisi',
            'kode_cabang.unique' => 'Kode sudah digunakan, silakan gunakan kode lain',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $market = Market::create([
                'nama' => $request->nama,
                'kode_cabang' => $request->kode_cabang,
            ]);

            // Assign creator sebagai admin market (kecuali superadmin)
            if (!Auth::user()->isSuperAdmin()) {
                $market->users()->attach(Auth::id(), [
                    'market_role' => 'admin',
                    'is_active' => true
                ]);
            }

            DB::commit();

            return response()->json([
                'data' => $market,
                'message' => 'Market berhasil dibuat'
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal membuat market: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $market = Market::find($id);

        if (!$market) {
            return response()->json(['message' => 'Market tidak ditemukan'], 404);
        }

        // Check access
        if (!$market->userHasAccess(Auth::id())) {
            return response()->json(['message' => 'Tidak memiliki akses ke market ini'], 403);
        }

        $user = Auth::user();
        $userRoleInMarket = $user->getRoleInMarket($id);

        return response()->json([
            'data' => $market,
            'user_role_in_market' => $userRoleInMarket,
            'message' => 'Market ditemukan'
        ]);
    }

    public function update(Request $request, Market $market)
    {
        // Check access
        if (!$market->userHasAccess(Auth::id())) {
            return response()->json(['message' => 'Tidak memiliki akses ke market ini'], 403);
        }

        // Hanya admin market atau superadmin yang bisa update
        $userRole = Auth::user()->getRoleInMarket($market->id);
        if (!in_array($userRole, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Tidak memiliki izin untuk mengupdate market'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'kode_cabang' => 'required|string|max:255|unique:markets,kode_cabang,' . $market->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $market->update($request->all());

        return response()->json([
            'message' => 'Market berhasil diupdate',
            'data' => $market
        ]);
    }

    public function delete(Market $market)
    {
        // Check access
        if (!$market->userHasAccess(Auth::id())) {
            return response()->json(['message' => 'Tidak memiliki akses ke market ini'], 403);
        }

        // Hanya admin market atau superadmin yang bisa delete
        $userRole = Auth::user()->getRoleInMarket($market->id);
        if (!in_array($userRole, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Tidak memiliki izin untuk menghapus market'], 403);
        }

        $market->delete();
        return response()->json(['message' => 'Market berhasil dihapus']);
    }

    // Assign user ke market
    public function assignUser(Request $request, Market $market)
    {
        if (!$market->userHasAccess(Auth::id())) {
            return response()->json(['message' => 'Tidak memiliki akses ke market ini'], 403);
        }

        $userRole = Auth::user()->getRoleInMarket($market->id);
        if (!in_array($userRole, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Tidak memiliki izin untuk assign user'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'market_role' => 'required|in:admin,kasir,supervisor'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check apakah user sudah assigned
        if ($market->users()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'User sudah memiliki akses ke market ini'], 400);
        }

        $market->users()->attach($request->user_id, [
            'market_role' => $request->market_role,
            'is_active' => true
        ]);

        return response()->json(['message' => 'User berhasil ditambahkan ke market']);
    }

    // Remove user dari market
    public function removeUser(Request $request, Market $market)
    {
        if (!$market->userHasAccess(Auth::id())) {
            return response()->json(['message' => 'Tidak memiliki akses ke market ini'], 403);
        }

        $userRole = Auth::user()->getRoleInMarket($market->id);
        if (!in_array($userRole, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Tidak memiliki izin untuk remove user'], 403);
        }

        $market->users()->detach($request->user_id);

        return response()->json(['message' => 'User berhasil dihapus dari market']);
    }

    public function detailStokToko($id_supplie)
    {
        $user = Auth::user();
        $accessibleMarkets = $user->getAccessibleMarkets()->pluck('id');

        $markets = Pengiriman::where('id_supplie', $id_supplie)
                    ->whereIn('id_market', $accessibleMarkets)
                    ->pluck('id_market')
                    ->unique();

        $detailPerToko = [];

        foreach ($markets as $marketId) {
            $market = Market::find($marketId);
            if (!$market) continue;

            $jumlahKirim = Pengiriman::where('id_supplie', $id_supplie)
                                ->where('id_market', $marketId)
                                ->sum('jumlah_kirim');

            $jumlahTerjual = Penjualan::where('id_supplie', $id_supplie)
                                ->where('id_market', $marketId)
                                ->sum('terjual');

            $stokToko = $jumlahKirim - $jumlahTerjual;

            $detailPerToko[] = [
                'id_supplie' => $id_supplie,
                'id_toko' => $marketId,
                'nama_toko' => $market->nama,
                'detail_stok_toko' => $stokToko,
                'user_role_in_market' => $user->getRoleInMarket($marketId)
            ];
        }

        return response()->json([
            'success' => true,
            'rincian_stok_per_toko' => $detailPerToko
        ]);
    }

    // Get users yang memiliki akses ke market
    public function getMarketUsers(Market $market)
    {
        if (!$market->userHasAccess(Auth::id())) {
            return response()->json(['message' => 'Tidak memiliki akses ke market ini'], 403);
        }

        $users = $market->users()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'global_role' => $user->role,
                'market_role' => $user->pivot->market_role,
                'is_active' => $user->pivot->is_active,
                'assigned_at' => $user->pivot->created_at
            ];
        });

        return response()->json([
            'data' => $users,
            'market' => $market->nama
        ]);
    }
}