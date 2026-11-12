<?php

namespace App\Ldap\Rules;

use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryGroups;
use Illuminate\Database\Eloquent\Model as Eloquent;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\Model;

class ActiveDirectoryAccess implements Rule
{
    public function __construct(private ActiveDirectoryGroups $groups) {}

    public function passes(Model $user, ?Eloquent $model = null): bool
    {
        return $user instanceof User
            && $user->isEnabled()
            && (! $model?->exists || (bool) $model->getAttribute('enabled'))
            && (! LibrenmsConfig::get('auth_ad_require_groupmembership', true) || $this->groups->memberships($user) !== []);
    }
}
