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
        $permissionsSupplies = [
            'index-supplies',
            'create-supplies',
            'show-supplies',
            'update-supplies',
            'delete-supplies'
        ];

        // Buat permission-permission supplies jika belum ada
        foreach ($permissionsSupplies as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Buat permission lain (produk, market, kariawan, dll) jika diperlukan
        // Contoh produk:
        $permissionsProducts = [
            'index-products',
            'create-products',
            'show-products',
            'update-products',
            'delete-products'
        ];
        foreach ($permissionsProducts as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // (Bisa teruskan buat permission lain sesuai kebutuhan)

        // ========================
        // Role: superadmin (semua permission)
        // ========================
        $roleSuperadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $allPermissions = Permission::all();
        $roleSuperadmin->syncPermissions($allPermissions);

        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
            ]
        );
        if (!$superadmin->hasRole($roleSuperadmin)) {
            $superadmin->assignRole($roleSuperadmin);
        }

        // ========================
        // Role: admin (hanya lihat supplies)
        // ========================
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $roleAdmin->syncPermissions(['show-supplies']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Supplies',
                'password' => Hash::make('password123'),
            ]
        );
        if (!$admin->hasRole($roleAdmin)) {
            $admin->assignRole($roleAdmin);
        }

        // ========================
        // Role: user (hanya bisa lihat supplies)
        // ========================
        $roleUser = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $roleUser->syncPermissions(['show-supplies']);

        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User Biasa',
                'password' => Hash::make('password123'),
            ]
        );
        if (!$user->hasRole($roleUser)) {
            $user->assignRole($roleUser);
        }
    }
}
