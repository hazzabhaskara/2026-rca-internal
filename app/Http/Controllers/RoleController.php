<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'guard_name' => 'nullable',
            'permissions' => 'array|nullable'
        ]);

        // Buat role baru
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web'
        ]);

        // Assign permissions jika ada
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', 'Role berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'guard_name' => 'nullable',
            'permissions' => 'array|nullable'
        ]);

        // Update data role
        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web'
        ]);

        // Sync ulang permissions-nya
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]); // kosongkan jika tidak ada
        }

        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', 'Role berhasil dihapus.');
    }
}
