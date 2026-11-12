<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User;

class ActiveDirectoryUser extends User
{
    protected ?string $connection = 'active_directory';
}
