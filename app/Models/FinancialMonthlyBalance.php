<?php

namespace App\Models;

use App\Models\FinancialAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialMonthlyBalance extends Model
{
    protected $fillable = [
        'financial_account_id',
        'reference_month',
        'initial_balance',
        'total_in',
        'total_out',
        'final_balance',
        'is_closed',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'total_in' => 'decimal:2',
        'total_out' => 'decimal:2',
        'final_balance' => 'decimal:2',
        'is_closed' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    /**
     * Scope para filtrar por mês de referência (ex: '2026-09').
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $yearMonth
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMonth($query, string $yearMonth)
    {
        return $query->where('reference_month', $yearMonth);
    }
}
