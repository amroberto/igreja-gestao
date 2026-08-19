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
}
