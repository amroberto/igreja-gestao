<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Community;
use App\Models\ContributionLetter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyConfig extends Model
{
    protected $table = 'monthly_configs';

    protected $fillable = [
        'community_id',
        'reference_date',
        'motto',
        'observations',
        'image_path',
    ];

    protected $casts = [
        'reference_date' => 'date:Y-m',
    ];

    public function getReferenceAttribute(): string
    {
        return Carbon::parse($this->reference_date)->format('Y-m');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(ContributionLetter::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}
