<?php

namespace App\Ldap\Scopes;

use App\Facades\LibrenmsConfig;
use LdapRecord\Models\Model;
use LdapRecord\Models\Scope;
use LdapRecord\Query\Model\Builder;

class ActiveDirectoryUsers implements Scope
{
    public function apply(Builder $query, Model $model): void
    {
        $query->rawFilter('(!(useraccountcontrol:1.2.840.113556.1.4.803:=2))');
        if ($filter = LibrenmsConfig::get('auth_ad_user_filter')) {
            $query->rawFilter(\LdapRecord\Query\Filter\Parser::parse($filter));
        }
    }
}
