<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',      // referência para o dono do cartão
        'asaas_card_id',  // id do cartão na API Asaas
        'last_digits',    // últimos 4 dígitos do cartão
        'brand',          // bandeira (Visa, Mastercard, etc)
        'holder_name',    // nome do titular (opcional)
        'expiration_date' // validade (opcional)
    ];

    // Relação com o membro dono do cartão
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
