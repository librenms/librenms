<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait ChecksGlobalPermissions
{
    protected ?string $globalPrefix = null;

    protected function hasGlobalPermission(User $user, string $action, bool $allowUserViewItemizedPermissions = false): bool
    {
        if ($allowUserViewItemizedPermissions && $action === 'view' && $user->hasRole('user')) {
            return true; // user role has all view permissions
        }

        // Guess prefix
        $this->globalPrefix ??= Str::kebab(Str::before(class_basename($this), 'Policy'));

        return $this->hasPermission($user, "$this->globalPrefix.$action");
    }

    /**
     * Check a permission directly, without the global-read gate shortcut.
     *
     * Use this for permissions that must stay explicit, such as config backup access.
     */
    protected function hasPermission(User $user, string $permission): bool
    {
        try {
            return $user->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist) {
            // do not log, there is no problem with permissions not existing

            return false;
        }
    }
}
