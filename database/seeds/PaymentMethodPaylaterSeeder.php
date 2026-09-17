<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodPaylaterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('payment_methods')->insert([
            [
                'kode_jenis' => 'PAYLATER',
                'nama_jenis' => 'PAYLATER',
                'status' => 1,
                'status_payment' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
