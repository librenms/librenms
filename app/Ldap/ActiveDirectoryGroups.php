<?php

namespace App\Ldap;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\Group;
use LdapRecord\Models\ActiveDirectory\User;
use LibreNMS\Enum\LegacyAuthLevel;

class ActiveDirectoryGroups
{
    /**
     * @return list<string>
     */
    public function memberships(User $user): array
    {
        $memberships = [];
        $recursiveGroups = null;
        foreach (array_keys(LibrenmsConfig::get('auth_ad_groups', [])) as $name) {
            $query = Group::on($user->getConnectionName())->where('cn', '=', $name);
            if ($filter = LibrenmsConfig::get('auth_ad_group_filter')) {
                $query->rawFilter(\LdapRecord\Query\Filter\Parser::parse($filter));
            }
            $groups = $query->limit(2)->get();
            $isMember = false;
            if (count($groups) === 1) {
                $recursiveGroups ??= $user->groups()->recursive()->get('objectclass');
                $isMember = $recursiveGroups->exists($groups[0]);
            }
            if (LibrenmsConfig::get('auth_ad_debug')) {
                Log::debug('Active Directory group membership check', [
                    'group' => $name,
                    'matching_groups' => count($groups),
                    'is_member' => $isMember,
                    'user_dn' => $user->getDn(),
                ]);
            }
            if ($isMember) {
                $memberships[] = $name;
            }
        }

        return $memberships;
    }

    /**
     * @return list<string>
     */
    public function roles(User $user): array
    {
        $roles = [];
        if (! LibrenmsConfig::get('auth_ad_require_groupmembership', true) && LibrenmsConfig::get('auth_ad_global_read')) {
            $roles[] = 'global-read';
        }
        foreach ($this->memberships($user) as $name) {
            $data = LibrenmsConfig::get('auth_ad_groups')[$name];
            if (isset($data['roles']) && is_array($data['roles'])) {
                $roles = array_merge($roles, $data['roles']);
            } elseif (isset($data['level']) && $role = LegacyAuthLevel::tryFrom((int) $data['level'])?->getName()) {
                $roles[] = $role;
            }
        }

        return array_values(array_unique($roles));
    }
}
