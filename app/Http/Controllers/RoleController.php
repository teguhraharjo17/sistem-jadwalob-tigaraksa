<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = Role::withCount('users')->get();
        return view('pages.roles.index', compact('roles'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'permissions' => $validated['permissions'] ?? [],
        ]);

        $role->users_count = 0;

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil ditambahkan.',
            'role' => $role
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return response()->json([
            'role' => $role
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $rules = [
            'permissions' => 'nullable|array',
        ];

        if (in_array((int)$id, [1, 2, 3])) {
            // Default roles: Name cannot be changed, permissions can be changed
            $validated = $request->validate($rules);
            $defaultNames = [1 => 'Super Admin', 2 => 'Admin', 3 => 'User'];
            $updateData = [
                'name' => $defaultNames[(int)$id],
                'permissions' => $validated['permissions'] ?? [],
            ];
        } else {
            // Custom roles: Both name and permissions can be changed
            $rules['name'] = 'required|string|max:191|unique:roles,name,' . $id;
            $validated = $request->validate($rules);
            $updateData = [
                'name' => $validated['name'],
                'permissions' => $validated['permissions'] ?? [],
            ];
        }

        $role->update($updateData);
        $role->loadCount('users');

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil diperbarui.',
            'role' => $role
        ]);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting default roles (Super Admin, Admin, User)
        if (in_array((int)$id, [1, 2, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'Role bawaan sistem tidak boleh dihapus.'
            ], 403);
        }

        // Prevent deleting roles that have users assigned to them
        if ($role->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak dapat dihapus karena masih digunakan oleh beberapa user.'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dihapus.'
        ]);
    }
}
