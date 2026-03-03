<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait ChecksGlobalPermissions
{
    protected ?string $prefix = null;

    protected function hasGlobalPermission(User $user, string $action): bool
    {
        // Guess prefix
        $this->prefix ??= Str::kebab(Str::before(class_basename($this), 'Policy'));

        try {
            return $user->hasPermissionTo("$this->prefix.$action");
        } catch (PermissionDoesNotExist $e) {
            Log::error($e->getMessage());

            return false;
        }
    }
}
