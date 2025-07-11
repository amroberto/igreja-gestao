<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Models\Contribution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory;

    use SoftDeletes;
    
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
    ];

    protected $attributes = [
        'is_active' => true,
        'is_contributor' => false,
        'delivery_method' => 'Postal Mail',
        'payment_method' => 'Transferência Bancária',
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

    public function contributions()
      {
          return $this->hasMany(Contribution::class);
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

    public function creditCards():HasMany
    {
        return $this->hasMany(CreditCard::class);
    }
}
