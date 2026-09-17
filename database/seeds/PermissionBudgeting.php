<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionBudgeting extends Seeder
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
            'budgeting.access',
            'create_budget.access',
            'create_budget.create',
            'create_budget.update',
            'create_budget.delete',

            'approval_step.access',
            'approval_step.create',
            'approval_step.update',
            'approval_step.delete',

            'rekening.access',
            'rekening.create',
            'rekening.update',
            'rekening.delete',

            'budget_submission.access',
            'budget_submission.create',
            'budget_submission.update',
            'budget_submission.delete',

            'budget_disbursement.access',
            'budget_disbursement.create',
            'budget_disbursement.update',
            'budget_disbursement.delete',

            'budget_realization.access',
            'budget_realization.create',
            'budget_realization.update',
            'budget_realization.delete',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
