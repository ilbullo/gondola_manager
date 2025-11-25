<?php

namespace App\Policies;

use App\Models\WorkAssignment;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkAssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function view(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function update(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function delete(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function restore(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }

    public function forceDelete(User $user, WorkAssignment $workAssignment): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::BANCALE]);
    }
}