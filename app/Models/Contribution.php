<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\FinancialEntity;
use App\Enums\PaymentMethodEnum;
use App\Enums\StatusPaymentEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'financial_entity_id',
        'community_id',
        'contribution_date',
        'amount',
        'start_date',
        'end_date',
        'description',
        'payment_method',
        'status',
        'asaas_payment_id',
        'asaas_boleto_url',
        'is_paid',
    ];

    protected $casts = [
        'contribution_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_method' => PaymentMethodEnum::class,
        'status' => StatusPaymentEnum::class,
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function financial_entity(): BelongsTo
    {
        return $this->belongsTo(FinancialEntity::class);
    }

    public function getPeriodAttribute()
    {
        // Configura a localização para português
        Carbon::setLocale('pt_BR');

        // Converte a data de início para Carbon
        $start = Carbon::parse($this->start_date);
        $formattedStart = strtolower($start->translatedFormat('M/Y')); // Formato: jan/2025

        // Verifica se end_date existe e é válido
        if (!empty($this->end_date)) {
            try {
                $end = Carbon::parse($this->end_date);
                $formattedEnd = strtolower($end->translatedFormat('M/Y')); // Formato: abr/2025
                return "{$formattedStart} a {$formattedEnd}";
            } catch (\Exception $e) {
                // Se end_date for inválido, retorna apenas start_date
                return $formattedStart;
            }
        }

        // Se end_date for null ou vazio, retorna apenas start_date
        return $formattedStart;
    }

    public function getMonthsPaid()
    {
        // Verifica se start_date existe
        if (empty($this->start_date)) {
            return 0;
        }

        // Converte start_date para Carbon
        $start = Carbon::parse($this->start_date)->startOfMonth();

        // Se end_date for null ou vazio, retorna 1 (apenas o mês de start_date)
        if (empty($this->end_date)) {
            return 1;
        }

        // Converte end_date para Carbon
        try {
            $end = Carbon::parse($this->end_date)->startOfMonth();
            // Calcula a diferença em meses, incluindo os meses inicial e final
            $months = $start->diffInMonths($end) + 1;
            return $months >= 0 ? $months : 0;
        } catch (\Exception $e) {
            // Se end_date for inválido, retorna 1 (apenas o mês de start_date)
            return 1;
        }
    }
}
