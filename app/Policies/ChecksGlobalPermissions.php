<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;

trait ChecksGlobalPermissions
{
    protected ?string $prefix = null;

    protected function hasGlobalPermission(User $user, string $action): bool
    {
        // Guess prefix
        $this->prefix ??= Str::kebab(Str::before(class_basename($this), 'Policy'));

        return $user->hasPermissionTo("$this->prefix.$action");
    }
}
