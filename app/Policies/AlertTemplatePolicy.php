<?php

namespace App\Policies;

use App\Models\AlertTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlertTemplatePolicy
{
    use HandlesAuthorization;

    public function allowRestify(User $user = null): bool
    {
        return $user !== null && $user->hasGlobalRead();
    }

    public function viewAny(User $user): bool
    {
        return $user->hasGlobalRead();
    }

    public function view(User $user, AlertTemplate $alertTemplate): bool
    {
        return $user->hasGlobalRead();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AlertTemplate $alertTemplate): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AlertTemplate $alertTemplate): bool
    {
        return $user->isAdmin();
    }
}
