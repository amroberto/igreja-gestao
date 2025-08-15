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
        'observations'
    ];

    protected $dates = ['reference_date'];

    /**
     * [Description for getReferenceAttribute]
     *
     * @return [type]
     * 
     */
    public function getReferenceAttribute()
    {
        return $this->reference_date->format('m/Y');
    }

    /**
     * [Description for community]
     *
     * @return BelongsTo
     * 
     */
    public function community():  BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * [Description for letters]
     *
     * @return HasMany
     * 
     */
    public function letters():HasMany
    {
        return $this->hasMany(ContributionLetter::class);
    }
}
