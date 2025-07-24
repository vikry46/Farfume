<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Bersihkan cache Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        //  Ambil authorities dari config
        $authorities = config('permission.authorities');

        //  Flatten authorities ke 1 dimensi array permissions
        $permissions = collect($authorities)->flatten()->unique()->toArray();

        //  Buat permissions kalau belum ada
        foreach ($permissions as $permission) {
             ['name' => $permission, 'guard_name' => 'sanctum'];
        }

        //  Buat roles
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $user = Role::firstOrCreate(['name' => 'user']);

        //  Superadmin: semua permission
        $superadmin->givePermissionTo(Permission::all());

        //  Admin: semua CRUD tanpa read-only grafik/export kalau mau
        $adminPermissions = collect($authorities)->except(['grafik', 'export'])
            ->flatten()->unique()->toArray();
        $admin->givePermissionTo($adminPermissions);

        //  Manager: hanya read-only permissions (index + show)
        $managerPermissions = collect($permissions)->filter(function ($p) {
            return str_starts_with($p, 'index-') || str_starts_with($p, 'show-') || str_starts_with($p, 'view-');
        })->toArray();
        $manager->givePermissionTo($managerPermissions);

        //  User: akses sangat terbatas
        $userPermissions = [
            'index-products', 'show-products',
            'index-markets', 'show-markets',
            'index-penjualan', 'show-penjualan',
            'index-stokmarket',
        ];
        $user->givePermissionTo($userPermissions);

        //  Buat superadmin user kalau belum ada
        $superadminUser = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            ['name' => 'Super Admin', 'password' => bcrypt('password123')]
        );
        $superadminUser->assignRole('superadmin');

        //  Buat admin user kalau belum ada
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password123')]
        );
        $adminUser->assignRole('admin');
    }
}
