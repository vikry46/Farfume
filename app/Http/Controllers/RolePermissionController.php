<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Response;
use Exception;

class RolePermissionController extends Controller
{
    /**
     * Ambil semua permissions + yang dimiliki role.
     */
    public function index($roleId)
    {
        try {
            $role = Role::with('permissions')->findOrFail($roleId);
            $permissions = Permission::all();

            return response()->json([
                'success' => true,
                'message' => 'Role & permissions',
                'data' => [
                    'role' => $role,
                    'all_permissions' => $permissions
                ],
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sinkronisasi permissions ke role.
     */
    public function sync(Request $request, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);

            $request->validate([
                'permissions' => 'array',
            ]);

            $role->syncPermissions($request->permissions);

            return response()->json([
                'success' => true,
                'message' => 'Permissions berhasil disinkronkan ke role.',
                'data' => $role->load('permissions'),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi permissions',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Revoke satu permission dari role.
     */
    public function revokePermission(Request $request, $roleId)
    {
        try {
            $role = Role::findOrFail($roleId);

            $request->validate([
                'permission' => 'required',
            ]);

            $role->revokePermissionTo($request->permission);

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dicabut dari role.',
                'data' => $role->load('permissions'),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencabut permission',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
