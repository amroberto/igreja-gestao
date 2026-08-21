<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    /** @use HasFactory<\Database\Factories\ParishFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'cnpj',
        'cep',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'phone',
        'cellphone',
        'email',
        'website',
        'logo',
        'pix_key',
        'pix_key_type',
        'pix_receiver_name',
        'pix_city',
        'pix_static_enabled',
    ];

    protected $casts = [
        'pix_static_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    /**
     * Higieniza a chave PIX antes de salvar no banco de dados.
     */
    protected function setPixKeyAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['pix_key'] = null;
            return;
        }

        $type = strtolower($this->attributes['pix_key_type'] ?? '');

        // Se for CNPJ ou CPF, salva apenas os dígitos numéricos
        if (in_array($type, ['cnpj', 'cpf']) || preg_match('/^\d{2,3}\.\d{3}/', $value)) {
            $this->attributes['pix_key'] = preg_replace('/[^0-9]/', '', $value);
        } else {
            $this->attributes['pix_key'] = trim($value);
        }
    }
}
