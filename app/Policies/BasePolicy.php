<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;

abstract class BasePolicy
{
    protected string $prefix;

    public function __construct()
    {
        $this->prefix ??= Str::kebab(Str::before(class_basename($this), 'Policy'));
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo("$this->prefix.viewAny");
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo("$this->prefix.create");
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionTo("$this->prefix.update");
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo("$this->prefix.delete");
    }
}
