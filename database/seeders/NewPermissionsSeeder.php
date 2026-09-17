<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NewPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk menambahkan permissions baru yang diperlukan
     * untuk routes yang sebelumnya tidak di-protect.
     */
    public function run()
    {
        // Permissions baru yang perlu ditambahkan
        $permissions = [
            'linked_account_asset.access',
            'item_category.access',
            'items.access',
            'kategori_asset.access',
            'crew_shift_karyawan.access',
            'group_unit.access',
            'lokasi.access',
            'masa_manfaat.access',
            'metode_penyusutan.access',
            'tangible_asset.access',
            'intangible_asset.access',
        ];

        // Buat permissions jika belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Optional: Assign semua permission baru ke role Admin
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            $this->command->info('All new permissions assigned to Admin role.');
        }

        $this->command->info('Created ' . count($permissions) . ' new permissions.');
    }
}
