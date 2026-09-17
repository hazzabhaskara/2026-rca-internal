<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('location_inventories')->insert([
            [
                'kode_lokasi' => 'Primary Location',
                'Deskripsi' => 'Primary Location For All Inventory Items',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
    // cekcek
}
