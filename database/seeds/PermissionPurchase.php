<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionPurchase extends Seeder
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

            'purchase.access',

            'setup_purchase.access',
            'option_purchase.access',
            'linked_account_purchase.access',


            'purchase_order.access',
            'purchase_order.create',
            'purchase_order.view',
            'purchase_order.update',
            'purchase_order.delete',

            'purchase_invoice.access',
            'purchase_invoice.create',
            'purchase_invoice.view',
            'purchase_invoice.update',
            'purchase_invoice.delete',

            'prepayment_purchase.access',
            'prepayment_purchase.create',
            'prepayment_purchase.view',
            'prepayment_purchase.update',
            'prepayment_purchase.delete',

            'payment_purchase.access',
            'payment_purchase.create',
            'payment_purchase.view',
            'payment_purchase.update',
            'payment_purchase.delete',

            'payment_expense.access',
            'payment_expense.create',
            'payment_expense.view',
            'payment_expense.update',
            'payment_expense.delete',

            'vendor.access',
            'vendor.create',
            'vendor.view',
            'vendor.update',
            'vendor.delete',


        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
