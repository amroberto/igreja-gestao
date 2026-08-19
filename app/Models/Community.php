<?php

namespace App\Models;

use App\Models\FinancialEntity;
use App\Models\Member;
use App\Models\Parish;
use App\Models\User;
use App\Models\UserCommunity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    /** @use HasFactory<\Database\Factories\CommunityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'cnpj',
        'parish_id',
        'cep',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'phone',
        'cellphone',
        'email',
        'website',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ]; 
    
    public function parish():BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }
  
    public function financialEntities():HasMany
    {
        return $this->hasMany(FinancialEntity::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_community')
                    ->using(UserCommunity::class)
                    ->withTimestamps();
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'community_id');
    }
}
