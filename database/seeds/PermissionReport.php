<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionReport extends Seeder
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
            'report_menu.access',
            'buku_besar.access',
            'trial_balance.access',
            'income_statement.access',
            'income_statement_departement.access',
            'neraca.access',
            'neraca_departemen.access',

            'arus_kas.access',


            'documents.access',
            'sales_document.access',
            'purchases_document.access',
            'taxes.access',
            'taxes.create',
            'taxes.view',
            'taxes.update',
            'taxes.delete',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
