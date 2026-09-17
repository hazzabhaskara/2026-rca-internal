<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;


class PermissionAsset extends Seeder
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
            // Modul asset
            'asset.access',
            'setup_asset.access',

            'kategori_asset.access',
            'kategori_asset.create',
            'kategori_asset.view',
            'kategori_asset.update',
            'kategori_asset.delete',

            // lokasi asset 
            'lokasi_asset.access',
            'lokasi_asset.create',
            'lokasi_asset.view',
            'lokasi_asset.update',
            'lokasi_asset.delete',


            // masa manfaat
            'masa_manfaat.access',
            'masa_manfaat.create',
            'masa_manfaat.view',
            'masa_manfaat.update',
            'masa_manfaat.delete',

            // metode penyusutan

            'metode_penyusutan.access',
            'metode_penyusutan.create',
            'metode_penyusutan.view',
            'metode_penyusutan.update',
            'metode_penyusutan.delete',

            // metode penyusutan

            'metode_penyusutan.access',
            'metode_penyusutan.create',
            'metode_penyusutan.view',
            'metode_penyusutan.update',
            'metode_penyusutan.delete',

            // tangible asset
            'tangible_asset.access',
            'tangible_asset.create',
            'tangible_asset.view',
            'tangible_asset.update',
            'tangible_asset.delete',

            // intangible asset
            'intangible_asset.access',
            'intangible_asset.create',
            'intangible_asset.view',
            'intangible_asset.update',
            'intangible_asset.delete',

            // monthly process
            'monthly_process.access',
            'monthly_process.create',
            'monthly_process.view',
            'monthly_process.update',
            'monthly_process.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
