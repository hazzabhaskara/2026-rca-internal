<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemAccount extends Model
{
    //
    protected $fillable = [
        'item_id',
        'asset_account_id',
        'revenue_account_id',
        'cogs_account_id',
        'variance_account_id',
        'expense_account_id',
        'account_receivable_id'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function account()
    {
        return $this->belongsTo(chartOfAccount::class);
    }
    public function assetAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'asset_account_id');
    }

    public function revenueAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'revenue_account_id');
    }

    public function expenseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'expense_account_id');
    }

    public function cogsAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cogs_account_id');
    }

    public function varianceAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'variance_account_id');
    }
    public function accountReceivable()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_receivable_id');
    }
}
