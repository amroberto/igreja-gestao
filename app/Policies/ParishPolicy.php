<?php

namespace App\Policies;

use App\Models\Parish;
use App\Models\User;
use App\Enums\UserTypeEnum;

class ParishPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->userType === UserTypeEnum::ADMIN;
    }

    public function view(User $user, Parish $parish): bool
    {
        return $user->userType === UserTypeEnum::ADMIN;
    }

    public function create(User $user): bool
    {
        return $user->userType === UserTypeEnum::ADMIN;
    }

    public function update(User $user, Parish $parish): bool
    {
        return $user->userType === UserTypeEnum::ADMIN;
    }

    public function delete(User $user, Parish $parish): bool
    {
        return $user->userType === UserTypeEnum::ADMIN;
    }

    public function restore(User $user, Parish $parish): bool
    {
        return $user->userType === UserTypeEnum::ADMIN;
    }

    public function forceDelete(User $user, Parish $parish): bool
    {
        return $user->userType === UserTypeEnum::ADMIN;
    }
}
