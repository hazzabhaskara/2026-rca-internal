<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('roles.permissions', compact('roles', 'permissions'));
    }

    public function update(Request $request)
    {
        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions ?? []); // jika kosong, hapus semua

        return redirect()->route('roles.permissions.index')->with('success', 'Permissions berhasil diperbarui untuk role: ' . $role->name);
    }
}
