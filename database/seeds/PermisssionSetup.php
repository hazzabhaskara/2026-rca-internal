<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class PermisssionSetup extends Seeder
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
            'setup_seluruh.access',
            'company.access',
            'general.access',
            'reports_setup.access',

            'setting_setup.access',


            'company_profile.access',
            'taxpayers_company.access',


            'year_book.access',
            'year_book.create',
            'year_book.view',
            'year_book.update',
            'year_book.delete',

            'numbering.access',
            'numbering.create',
            'numbering.view',


            'klasifikasi_akun.access',
            'klasifikasi_akun.create',
            'klasifikasi_akun.view',
            'klasifikasi_akun.update',
            'klasifikasi_akun.delete',


            'chart_of_account.access',
            'chart_of_account.create',
            'chart_of_account.view',
            'chart_of_account.update',
            'chart_of_account.delete',

            'departement.access',
            'departement.create',
            'departement.view',
            'departement.update',
            'departement.delete',

            'linked_account_setup.access',
            'linked_account_setup.create',
            'linked_account_setup.view',
            'linked_account_setup.update',
            'linked_account_setup.delete',

            'sales_taxes.access',
            'sales_taxes.create',
            'sales_taxes.view',
            'sales_taxes.update',
            'sales_taxes.delete',

            // report 
            'report_account.access',
            'report_klasifikasi_akun.access',
            'report_departemen_account.access',



        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
