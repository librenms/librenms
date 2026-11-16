<?php

namespace App\Ldap\Rules;

use App\Facades\LibrenmsConfig;
use App\Ldap\ActiveDirectoryGroups;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Log;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Models\Model;

class ActiveDirectoryAccess implements Rule
{
    public function __construct(private readonly ActiveDirectoryGroups $groups) {}

    public function passes(Model $user, ?Eloquent $model = null): bool
    {
        $reason = null;
        if (! $user instanceof User) {
            $reason = 'LDAP entry is not an Active Directory user';
        } elseif (! $user->isEnabled()) {
            $reason = 'Active Directory account is disabled';
        } elseif ($model?->exists && ! $model->getAttribute('enabled')) {
            $reason = 'LibreNMS account is disabled';
        } elseif (LibrenmsConfig::get('auth_ad_require_groupmembership', true) && $this->groups->memberships($user) === []) {
            $reason = 'User is not a member of any configured AD group';
        }

        if ($reason !== null) {
            Log::warning('Active Directory access denied', [
                'reason' => $reason,
                'dn' => $user->getDn(),
                'user_id' => $model?->getKey(),
                'configured_groups' => array_keys(LibrenmsConfig::get('auth_ad_groups', [])),
            ]);
        }

        return $reason === null;
    }
}
