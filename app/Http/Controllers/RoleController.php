<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Response;
use Exception;


class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->get();
    }

    public function show($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        }

        
        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role created',
            'role' => $role->load('permissions')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'message' => 'Role updated',
            'role' => $role->load('permissions')
        ]);
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            if ($role->users()->count() > 0) {
                return response()->json([
                    'error' => 'Role masih digunakan oleh user.'
                ], 400);
            }

            $role->delete();

            return response()->json(['message' => 'Role berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus role',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'array',
        ]);

        $role->syncPermissions($request->permissions);
        return response()->json([
            'message' => 'Permissions synced to role.',
            'role' => $role->load('permissions'),
        ]);
    }

    public function getAllPermissions()
    {
        return Permission::pluck('name');
    }
}
