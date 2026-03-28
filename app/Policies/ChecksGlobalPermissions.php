<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait ChecksGlobalPermissions
{
    protected ?string $globalPrefix = null;

    protected function hasGlobalPermission(User $user, string $action): bool
    {
        // Guess prefix
        $this->globalPrefix ??= Str::kebab(Str::before(class_basename($this), 'Policy'));

        try {
            return $user->hasPermissionTo("$this->globalPrefix.$action");
        } catch (PermissionDoesNotExist) {
            // do not log, there is no problem with permissions not existing

            return false;
        }
    }
}
