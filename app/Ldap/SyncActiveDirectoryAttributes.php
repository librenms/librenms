<?php

namespace App\Ldap;

use App\Models\User;
use LdapRecord\Models\Model;

class SyncActiveDirectoryAttributes
{
    public function handle(Model $ldap, User $user): void
    {
        $user->username = $ldap->getFirstAttribute('samaccountname');
        $user->realname = $ldap->getFirstAttribute('displayname') ?? $ldap->getFirstAttribute('name') ?? $user->username;
        $user->email = $ldap->getFirstAttribute('mail') ?? '';
        $user->can_modify_passwd = 0;
        if (! $user->exists) {
            $user->enabled = true;
        }
    }
}
