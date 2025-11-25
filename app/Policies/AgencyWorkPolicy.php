<?php

namespace App\Policies;

use App\Models\AgencyWork;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgencyWorkPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function view(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function update(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function delete(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function restore(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function forceDelete(User $user, AgencyWork $agencyWork): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }
}