<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionPayroll extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $permissions = [

            // payroll 
            'payroll.access',
            // setup
            'setup_payroll.access',
            // level karyawan modul
            'level_karyawan.access',
            'level_karyawan.create',
            'level_karyawan.view',
            'level_karyawan.update',
            'level_karyawan.delete',
            // unit /departement modul
            'unit.access',
            'unit.create',
            'unit.view',
            'unit.update',
            'unit.delete',

            // jabatan modul
            'jabatan.access',
            'jabatan.create',
            'jabatan.view',
            'jabatan.update',
            'jabatan.delete',
            // komponen penghasilan modul
            'komponen_penghasilan.access',
            'komponen_penghasilan.create',
            'komponen_penghasilan.view',
            'komponen_penghasilan.update',
            'komponen_penghasilan.delete',
            // employee modul
            'employee.access',
            'employee.create',
            'employee.view',
            'employee.update',
            'employee.delete',
            // komposisi gaji modul
            'komposisi_gaji.access',
            'komposisi_gaji.create',
            'komposisi_gaji.view',
            'komposisi_gaji.update',
            'komposisi_gaji.delete',
            // komposisi gaji modul
            'ptkp.access',
            'ptkp.create',
            'ptkp.view',
            'ptkp.update',
            'ptkp.delete',

            'tax_rates.access',
            'tax_rates.create',
            'tax_rates.view',
            'tax_rates.update',
            'tax_rates.delete',


            'pembayaran_gaji.access',
            'pembayaran_gaji.create',
            'pembayaran_gaji.view',
            'pembayaran_gaji.update',
            'pembayaran_gaji.delete',

            'pembayaran_gaji_nonstaff.access',
            'pembayaran_gaji_nonstaff.create',
            'pembayaran_gaji_nonstaff.view',
            'pembayaran_gaji_nonstaff.update',
            'pembayaran_gaji_nonstaff.delete',

            'slip_gaji.access',


            'slip_gaji_nonstaff.access',

            // process
            'process_payroll.access',

            // report 
            'report_payroll.access',

            'bonus_karyawan.access',




        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
