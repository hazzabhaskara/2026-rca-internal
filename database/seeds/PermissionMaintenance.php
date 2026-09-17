<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionMaintenance extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $permissions = [

            'maintenance.access',
            'start_new_year.access',
            'log_activity.access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
