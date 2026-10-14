<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssignedWatchdogPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'assigned_watchdog.access',
            'assigned_watchdog.update',
            'assigned_watchdog.override_closed',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // Assign to Admin (all 3 permissions)
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            if (isset($this->command)) {
                $this->command->info('AssignedWatchdog permissions granted to Admin role.');
            }
        }

        // Assign to Staff (access and update only; closed-period override is restricted)
        $staffRole = Role::where('name', 'Staff')->first();
        if ($staffRole) {
            $staffRole->givePermissionTo([
                'assigned_watchdog.access',
                'assigned_watchdog.update',
            ]);
            if (isset($this->command)) {
                $this->command->info('AssignedWatchdog access and update granted to Staff role.');
            }
        }

        // Reset cached roles and permissions again
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
