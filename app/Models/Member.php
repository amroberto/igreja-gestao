<?php

namespace App\Models;

use App\Enums\DeliveryMethodEnum;
use App\Enums\GenderEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Contribution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'cpf',
        'rg',
        'birth_date',
        'gender',
        'email',
        'phone',
        'cellphone',
        'cep',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'community_id',
        'user_id',
        'is_active',
        'is_contributor',
        'status',
        'marital_status',
        'father_name',
        'mother_name',
        'delivery_method',
        'payment_method',
        'due_day',
        'amount',
        'observations',
        'member_asaas_id'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'is_contributor' => 'boolean',
        'due_day' => 'integer',
        'amount' => 'decimal:2',
        'gender' => GenderEnum::class,
        'delivery_method' => DeliveryMethodEnum::class,
        'payment_method' => PaymentMethodEnum::class,
    ];

    protected $attributes = [
        'is_active' => true,
        'is_contributor' => false,
        'delivery_method' => 'Postal Mail',
        'payment_method' => 'transf_bancaria',
        'due_day' => 5,
        'amount' => 0.00,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Relação para carregar as contribuições quitadas/confirmadas (ordenadas por data decrescente)
     */
    public function confirmedContributions(): HasMany
    {
        return $this->hasMany(Contribution::class)
            ->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('is_paid', true);
            })
            ->orderBy('contribution_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relação para carregar as contribuições em aberto/pendentes (ordenadas por data decrescente)
     */
    public function pendingContributions(): HasMany
    {
        return $this->hasMany(Contribution::class)
            ->where(function ($q) {
                $q->where('status', 'pending')->orWhere('is_paid', false);
            })
            ->orderBy('contribution_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function getNameAttribute()
    {
        $nameParts = [$this->first_name];

        if ($this->middle_name) {
            $nameParts[] = $this->middle_name;
        }

        if ($this->last_name) {
            $nameParts[] = $this->last_name;
        }

        return implode(' ', $nameParts);
    }

    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }
}