<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionFiscal extends Seeder
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

            'fiscal.access',
            'perhitungan_pajak_penghasilan.access',

            // fiscal account 
            'fiscal_account.access',
            'fiscal_account.create',
            'fiscal_account.update',
            'fiscal_account.delete',
            'fiscal_account.view',

            // fiscal account persamaan

            'fiscal_account_persamaan.access',
            'fiscal_account_persamaan.create',
            'fiscal_account_persamaan.update',
            'fiscal_account_persamaan.delete',
            'fiscal_account_persamaan.view',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
