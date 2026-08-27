<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ParochialUnion extends Model
{
    protected $table = 'parochial_unions';

    protected $fillable = [
        'corporate_name',
        'trade_name',
        'cnpj',
        'phone',
        'cellphone',
        'cep',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'website',
        'email',
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

    use HasFactory;
    
    public function getLogoUrlAttribute()
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function getLogoPathAttribute()
    {
        return $this->logo ? Storage::path($this->logo) : null;
    }
    public function getLogoExistsAttribute()
    {
        return $this->logo ? Storage::exists($this->logo) : false;
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
