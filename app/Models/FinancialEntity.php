<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinancialEntity extends Model
{
    /** @use HasFactory<\Database\Factories\FinancialEntityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'community_id',
    ];

    public function community():BelongsTo
    {
        return $this->belongsTo(Community::class);
    }
}
