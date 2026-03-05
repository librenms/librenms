<?php

namespace App\Policies;

use App\Models\AlertRule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlertRulePolicy
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

    public function view(User $user, AlertRule $alertRule): bool
    {
        return $user->hasGlobalRead();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, AlertRule $alertRule): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, AlertRule $alertRule): bool
    {
        return $user->isAdmin();
    }
}
