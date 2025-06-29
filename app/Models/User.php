<?php

namespace App\Models;

use App\Enums\UserTypeEnum;
use App\Models\UserCommunity;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'user_type', 'cpf', 'community_id', 'status'];

    // Relacionamento: Um User pode ter vários Members

    public function member()
    {
        return $this->hasOne(Member::class);
    }


    public function isAdmin(): bool
    {
        return strtolower($this->user_type) === 'admin';
    }

    public function isMember(): bool
    {
        return strtolower($this->user_type) === 'membro';
    }

    public function isSec(): bool
    {
        return strtolower($this->user_type) === 'sec';
    }

    public function isDiretoria()
    {
        return strtolower($this->user_type) === 'diretoria';
    }

    public function isPresbiterio()
    {
        return strtolower($this->user_type) === 'presbiterio';
    }

    public function community()
    {
        return $this->belongsTo(Community::class, 'community_id');
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'user_community')
            ->using(UserCommunity::class)
            ->withTimestamps();
    }
}
