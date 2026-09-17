<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JamKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('jamkerjas')->insert([
            [
                'jam_masuk'  => '08:00:00',
                'jam_keluar' => '16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
    // cekcek
}
