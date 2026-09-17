<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionClosingHarian extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $permissions = [
            'linked_account_closing.access',
            'linked_account_closing.create',
            'linked_account_closing.update',
            'linked_account_closing.delete',

            'closing_harian.access',
            'closing_harian.create',
            'closing_harian.update',
            'closing_harian.delete',


        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
