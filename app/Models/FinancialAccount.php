<?php

namespace App\Models;

use App\Enums\AccountTypeEnum;
use App\Models\Community;
use App\Models\FinancialMonthlyBalance;
use App\Models\FinancialTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialAccount extends Model
{
    protected $fillable = [
        'community_id',
        'name',
        'type',
        'bank_name',
        'account_number',
        'agency_number',
        'opening_balance',
        'opening_balance_date',
        'is_active',
    ];

    protected $casts = [
        'type' => AccountTypeEnum::class,
        'opening_balance' => 'decimal:2',
        'opening_balance_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function monthlyBalances()
    {
        return $this->hasMany(FinancialMonthlyBalance::class);
    }

    public function getCurrentBalanceAttribute(): float
    {
        $credits = $this->transactions()->where('type', 'credit')->sum('amount');
        $debits = $this->transactions()->where('type', 'debit')->sum('amount');

        return (float) ($this->opening_balance + $credits - $debits);
    }

}
