<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSales extends Seeder
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
            'sales.access',
            'setup_sales.access',

            'option_sales.access',
            'option_sales.create',
            'option_sales.view',
            'option_sales.update',
            'option_sales.delete',

            'linked_account_sales.access',
            'linked_account_sales.create',
            'linked_account_sales.view',
            'linked_account_sales.update',
            'linked_account_sales.delete',


            'sales_discount.access',
            'sales_discount.create',
            'sales_discount.view',
            'sales_discount.update',
            'sales_discount.delete',

            'data.access',

            'payment_method.access',
            'payment_method.create',
            'payment_method.view',
            'payment_method.update',
            'payment_method.delete',

            'customers.access',
            'customers.create',
            'customers.view',
            'customers.update',
            'customers.delete',


            'sales_orders.access',
            'sales_orders.create',
            'sales_orders.view',
            'sales_orders.update',
            'sales_orders.delete',

            'sales_invoice.access',
            'sales_invoice.create',
            'sales_invoice.view',
            'sales_invoice.update',
            'sales_invoice.delete',

            'sales_person.access',
            'sales_person.create',
            'sales_person.view',
            'sales_person.update',
            'sales_person.delete',

            'deposits.access',
            'deposits.create',
            'deposits.view',
            'deposits.update',
            'deposits.delete',

            'receipts.access',
            'receipts.create',
            'receipts.view',
            'receipts.update',
            'receipts.delete',


        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
