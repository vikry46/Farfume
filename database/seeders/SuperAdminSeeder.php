<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Bersihkan cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Definisikan permission-permission supplies (contoh lengkap untuk supplies)
        $permissions = [
            // Supplies
            'index-supplies',
            'create-supplies',
            'show-supplies',
            'update-supplies',
            'delete-supplies',
            
            // Products
            'index-products',
            'create-products',
            'show-products',
            'update-products',
            'delete-products',
            
            // Markets
            'index-markets',
            'create-markets',
            'show-markets',
            'update-markets',
            'delete-markets',
            
            // Kariawan
            'index-kariawan',
            'create-kariawan',
            'show-kariawan',
            'update-kariawan',
            'delete-kariawan',
            
            // Pengiriman
            'index-pengiriman',
            'create-pengiriman',
            'show-pengiriman',
            'update-pengiriman',
            'delete-pengiriman',
            
            // Penjualan
            'index-penjualan',
            'create-penjualan',
            'show-penjualan',
            'update-penjualan',
            'delete-penjualan',
            
            // Barang Masuk
            'index-barangmasuk',
            'create-barangmasuk',
            'show-barangmasuk',
            'update-barangmasuk',
            'delete-barangmasuk',

            // ukuran botol
            'index-ukuran-botol',
            'create-ukuran-botol',
            'show-ukuran-botol',
            'update-ukuran-botol',
            'delete-ukuran-botol',
            
            // Market Produk
            'index-marketproduk',
            'create-marketproduk',
            
            // Stok Market
            'index-stokmarket',

            // Stock Gudang
            'index-gudang',
        ];

        // foreach ($permissions as $permission) {
        // Permission::create(['name' => $permission]);
        // }

        // Buat permission-permission supplies jika belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]); //->kenapa pakai first or create untuk mengghindari duplicasi
        }

        // Permission untuk market
     // Create roles
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user = Role::firstOrCreate(['name' => 'user']);
        $manager = Role::firstOrCreate(['name' => 'manager']);

        // Give all permissions to superadmin
        $superadmin->givePermissionTo(Permission::all());

        // Give specific permissions to admin
        $admin->givePermissionTo([
            'index-supplies', 'create-supplies', 'show-supplies', 'update-supplies',
            'index-products', 'create-products', 'show-products', 'update-products',
            'index-markets', 'create-markets', 'show-markets', 'update-markets',
            'index-kariawan', 'create-kariawan', 'show-kariawan', 'update-kariawan',
            'index-pengiriman', 'create-pengiriman', 'show-pengiriman', 'update-pengiriman',
            'index-penjualan', 'create-penjualan', 'show-penjualan', 'update-penjualan',
            'index-barangmasuk', 'create-barangmasuk', 'show-barangmasuk', 'update-barangmasuk',
            'index-ukuran-botol','create-ukuran-botol','show-ukuran-botol','update-ukuran-botol',
            'index-marketproduk', 'create-marketproduk',
            'index-stokmarket',
        ]);

        // Give read permissions to manager
        $manager->givePermissionTo([
            'index-supplies', 'show-supplies',
            'index-products', 'show-products',
            'index-markets', 'show-markets',
            'index-kariawan', 'show-kariawan',
            'index-pengiriman', 'show-pengiriman',
            'index-penjualan', 'show-penjualan',
            'index-barangmasuk', 'show-barangmasuk',
            'index-marketproduk',
            'index-stokmarket',
        ]);

        // Give limited permissions to user
        $user->givePermissionTo([
            'index-products', 'show-products',
            'index-markets', 'show-markets',
            'index-penjualan', 'show-penjualan',
            'index-stokmarket',
        ]);

        // Create a superadmin user if doesn't exist
        $superadminUser = User::where('email', 'superadmin@example.com')->first();
        if (!$superadminUser) {
            $superadminUser = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('password123'),
            ]);
        }
        $superadminUser->assignRole('superadmin');

        // Create an admin user if doesn't exist
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password123'),
            ]);
        }
        $adminUser->assignRole('admin');
    }
}