<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialCategory extends Model
{
    protected $fillable = [
        'account_plan_id',
        'name',
        'type',
        'active',
    ];

    public function accountPlan(): BelongsTo
    {
        return $this->belongsTo(AccountPlan::class, 'account_plan_id');
    }
}
