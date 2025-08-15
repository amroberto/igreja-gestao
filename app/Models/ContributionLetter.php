<?php

namespace App\Models;

use App\Models\Member;
use App\Models\Community;
use App\Models\MonthlyConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionLetter extends Model
{
    protected $table = 'contribution_letters';
    protected $fillable = [
        'member_id',
        'community_id',
        'monthly_config_id',
        'amount',
        'due_date',
        'pix_code',
        'delivery_method',
        'status'
    ];

    protected $dates = ['due_date'];

    /**
     * [Description for getReferenceAttribute]
     *
     * @return [type]
     * 
     */
    public function getReferenceAttribute()
    {
        return $this->monthlyConfig->reference;
    }

    /**
     * [Description for contribution]
     *
     * @return [type]
     * 
     */
    public function contribution()
    {
        return $this->hasOne(Contribution::class, 'id', 'id'); // Ajustado para compatibilidade com contributions existente
    }

    /**
     * [Description for member]
     *
     * @return [type]
     * 
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * [Description for community]
     *
     * @return [type]
     * 
     */
    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function monthlyConfig()
    {
        return $this->belongsTo(MonthlyConfig::class);
    }
}
