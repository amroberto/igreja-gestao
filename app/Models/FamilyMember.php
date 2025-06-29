<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RelationshipTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FamilyMember extends Model
{
    /** @use HasFactory<\Database\Factories\FamilyMembersFactory> */
    use HasFactory;

    protected $fillable = [ 'member_id', 'related_member_id', 'relationship_type'];

    protected $casts = [
        'relationship_type' => RelationshipTypeEnum::class,
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function relatedMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'related_member_id');
    }
}
