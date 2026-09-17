<?php

namespace App\Http\Controllers;

use App\Employee;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')->latest()->get();
        $roles = Role::all();
        $employee = Employee::all();
        $permissions = Permission::all();
        $activeTab = $request->get('tab', 'users');
        return view('users.index', compact('users', 'roles', 'activeTab', 'employee', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'nullable|exists:roles,id',
            'employee_id' => [
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) {
                    if (Employee::where('id', $value)->whereNotNull('user_id')->exists()) {
                        $fail('Karyawan ini sudah memiliki akun user.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'role_id'  => $request->role_id,
                'password' => Hash::make($request->password),
            ]);

            if ($request->filled('employee_id')) {
                Employee::where('id', $request->employee_id)
                    ->update(['user_id' => $user->id]);
            }

            if ($request->filled('role_id')) {
                $role = Role::findById($request->role_id);
                $user->assignRole($role);
            }
        });

        return redirect()
            ->route('users.index', ['tab' => 'users'])
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role_id'  => 'nullable|exists:roles,id',
        ]);

        $user->name    = $request->name;
        $user->email   = $request->email;
        $user->role_id = $request->role_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Reset roles dan assign baru jika role_id dikirim
        if ($request->filled('role_id')) {
            $user->syncRoles([]); // Hapus semua role lama
            $role = Role::findById($request->role_id);
            $user->assignRole($role);
        }

        return redirect()->route('users.index', ['tab' => 'users'])->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            // Jika user punya relasi employee, set user_id = null
            if ($user->employee) {
                $user->employee->update(['user_id' => null]);
            }

            // Hapus user-nya
            $user->delete();
        });

        return redirect()
            ->route('users.index', ['tab' => 'users'])
            ->with('success', 'User berhasil dihapus.');
    }
}
