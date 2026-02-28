<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;

trait ChecksGlobalPermissions
{
    protected string $prefix;

    protected function hasGlobalPermission(User $user, string $action): bool
    {
        // Guess prefix
        $prefix ??= Str::kebab(Str::before(class_basename($this), 'Policy'));

        return $user->hasPermissionTo("$prefix.$action");
    }
}
