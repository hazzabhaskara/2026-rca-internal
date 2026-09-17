<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $exists = DB::table('users')->where('email', 'admin@example.com')->first();

        if (!$exists) {
            DB::table('users')->insert([
                'name'       => 'Admin',
                'email'      => 'admin@example.com',
                'password'   => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        // $this->call(UsersTableSeeder::class);

        $this->call(PriceListInventorySeeder::class);
        $this->call(LocationInventorySeeder::class);
        $this->call(JamKerjaSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(PermissionAsset::class);
        $this->call(PermisssionSetup::class);
        $this->call(PermissionSales::class);
        $this->call(PermissionReport::class);
        $this->call(PermissionFiscal::class);
        $this->call(PermissionBudgeting::class);
        $this->call(PermissionMaintenance::class);
        $this->call(PermissionPayroll::class);
        $this->call(PermissionPayrollRca::class);
        $this->call(PermissionClosingHarian::class);
        $this->call(PermissionJournalEntry::class);
        $this->call(PermissionInventory::class);
        $this->call(PermissionPurchase::class);
        $this->call(PermissionSpecpose::class);
        $this->call(PaymentMethodPaylaterSeeder::class);
        $this->call(RoleSeeder::class);
        // cekcek
    }
}
