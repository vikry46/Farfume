<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Database\QueryException;
use Exception;
use Spatie\Permission\Models\Role;

class AuthenticateController extends Controller
{
    // 1. Tambah permission baru

    public function indexPermission()
    {
        try {
            // Ambil semua permission dari database
            $permissions = Permission::all();

            // Return response JSON dengan data permissions
            return response()->json(['permissions' => $permissions], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }

    public function storePermission(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:permissions,name',
            ]);

            $permission = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

            return response()->json(['message' => 'Permission created', 'permission' => $permission], 201);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }

    // 2. Tambah role baru
    public function storeRole(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name',
            ]);

            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

            return response()->json(['message' => 'Role created', 'role' => $role], 201);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database error', 'details' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'details' => $e->getMessage()], 500);
        }
    }

    // 3. Assign permission ke role
    public function givePermissionToRole(Request $request, $role)
    {
        try {
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            $role = Role::where('name', $role)->first();

            if (!$role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $role->syncPermissions($request->permissions);

            return response()->json(['message' => 'Permissions assigned to role']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to assign permissions',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // 4. Assign role ke user
    public function assignRoleToUser(Request $request, $user)
    {
        try {
            $request->validate([
                'role' => 'required|string|exists:roles,name',
            ]);

            $user = User::findOrFail($user);
            $user->assignRole($request->role);

            return response()->json(['message' => 'Role assigned to user']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to assign role', 'details' => $e->getMessage()], 500);
        }
    }

    // 5. Assign permission langsung ke user
    public function assignPermissionToUser(Request $request, $user)
    {
        try {
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            $user = User::findOrFail($user);
            $user->givePermissionTo($request->permissions);

            return response()->json(['message' => 'Permissions assigned to user']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to assign permissions', 'details' => $e->getMessage()], 500);
        }
    }
}
