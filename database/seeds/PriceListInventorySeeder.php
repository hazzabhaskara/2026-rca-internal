<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceListInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('price_list_inventories')->insert([
            [
                'description' => 'Reguler',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Preferred',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'description' => 'Web Price',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
    // cekcek
}
