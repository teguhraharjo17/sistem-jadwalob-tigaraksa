<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('role')->get();
        $roles = Role::all();
        return view('pages.users.index', compact('users', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'username' => ['required', 'string', 'max:191', 'unique:users,username'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'role_id' => $validated['role_id'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->load('role');
        $user->created_at_formatted = $user->created_at ? $user->created_at->translatedFormat('d M Y H:i') : '-';

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan.',
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => ['required', 'string', 'max:191'],
            'username' => ['required', 'string', 'max:191', 'unique:users,username,' . $id],
            'role_id' => ['required', 'exists:roles,id'],
        ];

        // Only validate password if it is filled
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'role_id' => $validated['role_id'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Prevent Super Admin from changing their own role to something else if they are the only Super Admin
        if ($id == auth()->id() && $user->role_id == 1 && $validated['role_id'] != 1) {
            $superAdminCount = User::where('role_id', 1)->count();
            if ($superAdminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda adalah satu-satunya Super Admin. Anda tidak dapat mengubah role Anda sendiri.'
                ], 422);
            }
        }

        $user->update($updateData);
        $user->load('role');
        $user->created_at_formatted = $user->created_at ? $user->created_at->translatedFormat('d M Y H:i') : '-';

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui.',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting oneself
        if ($id == auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'
            ], 422);
        }

        // Prevent deleting the last Super Admin
        if ($user->role_id == 1) {
            $superAdminCount = User::where('role_id', 1)->count();
            if ($superAdminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus satu-satunya Super Admin yang tersisa.'
                ], 422);
            }
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.'
        ]);
    }
}
