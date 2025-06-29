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
}
