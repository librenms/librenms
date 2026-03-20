<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Log;
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
        } catch (PermissionDoesNotExist $e) {
            Log::error($e->getMessage());

            return false;
        }
    }

    /**
     * Restify calls 'show' (defined in vendor/binaryk/laravel-restify/src/Bootstrap/RoutesDefinition.php) instead of 'view' for single-resource requests.
     */
    public function show(User $user, ...$args): bool
    {
        return $this->view($user, ...$args);
    }

    /**
     * Restify calls 'store' (defined in vendor/binaryk/laravel-restify/src/Bootstrap/RoutesDefinition.php) instead of 'create' for resource creation.
     */
    public function store(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }
}
