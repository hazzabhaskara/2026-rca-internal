<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionInventory extends Seeder
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
            // Modul inventory 

            'inventory.access',
            'setting_inventory.access',

            'options_inventory.access',
            'options_inventory.create',
            'options_inventory.view',
            'options_inventory.update',
            'options_inventory.delete',

            'price_list_inventory.access',
            'price_list_inventory.create',
            'price_list_inventory.view',
            'price_list_inventory.update',
            'price_list_inventory.delete',

            'lokasi_inventory.access',
            'lokasi_inventory.create',
            'lokasi_inventory.view',
            'lokasi_inventory.update',
            'lokasi_inventory.delete',

            'kategori_inventory.access',
            'kategori_inventory.create',
            'kategori_inventory.view',
            'kategori_inventory.update',
            'kategori_inventory.delete',

            //  inventory modul
            'inventory.create',
            'inventory.view',
            'inventory.update',
            'inventory.delete',


            'Build from Bom.access',
            'Build from Bom.create',
            'Build from Bom.view',
            'Build from Bom.update',
            'Build from Bom.delete',

            'Build from item assembly.access',
            'Build from item assembly.create',
            'Build from item assembly.view',
            'Build from item assembly.update',
            'Build from item assembly.delete',

            'Transfer inventory.access',
            'Transfer inventory.create',
            'Transfer inventory.view',
            'Transfer inventory.update',
            'Transfer inventory.delete',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
