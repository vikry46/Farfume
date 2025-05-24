<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'view dashboard']);

        // Buat role jika belum ada
        $admin = Role::firstOrCreate(['name' => 'superadmin']);
        $admin->givePermissionTo(['manage users', 'view dashboard']);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->givePermissionTo('view dashboard');
    }
}
