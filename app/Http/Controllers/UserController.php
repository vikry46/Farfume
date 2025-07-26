<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // GET /users
    public function index()
    {
        return User::with('roles')->get();
    }

    // GET /users/{id}
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }

        // POST /users
    public function store(Request $request)
    {
        $roles = Role::where('guard_name', 'web')->pluck('name')->toArray();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => ['required', Rule::in($roles)],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $role = Role::where('name', $validated['role'])->where('guard_name', 'web')->firstOrFail();
        $user->assignRole($role);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user->load('roles')
        ], 201);
    }
    // PUT /users/{id}
    public function update(Request $request, $id)
    {
        $roles = Role::pluck('name')->toArray();

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role'     => ['sometimes', Rule::in($roles)],
        ]);

        $user = User::findOrFail($id);

        $user->name  = $validated['name'] ?? $user->name;
        $user->email = $validated['email'] ?? $user->email;

        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->load('roles')
        ]);
    }

    // DELETE /users/{id}
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    // PATCH /users/{id}/assign-role
    public function assignRole(Request $request, $id)
    {
        $roles = Role::pluck('name')->toArray();

        $validated = $request->validate([
            'role' => ['required', Rule::in($roles)],
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles([$validated['role']]);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user'    => $user->load('roles')
        ]);
    }
}
