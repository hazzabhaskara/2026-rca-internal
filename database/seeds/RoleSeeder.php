<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Admin role if not exists
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        // Give all permissions to Admin role
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);

        // Assign Admin role to the Admin user
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser && !$adminUser->hasRole('Admin')) {
            $adminUser->assignRole('Admin');
        }

        $this->command->info('Admin role created and assigned to admin@example.com with ' . $allPermissions->count() . ' permissions.');
    }
}
