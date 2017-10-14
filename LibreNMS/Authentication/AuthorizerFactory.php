<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Interfaces\Authentication\Authorizer;

class AuthorizerFactory
{
    protected static $_instance;

    /**
     * Gets the authorizer based on the config
     *
     * @return Authorizer
     */
    public static function get()
    {
        if (!static::$_instance) {
            $configToClassMap = array(
                'mysql' => 'LibreNMS\Authentication\MysqlAuthorizer',
                'active_directory' => 'LibreNMS\Authentication\ActiveDirectoryAuthorizer',
                'ldap' => 'LibreNMS\Authentication\LdapAuthorizer',
                'radius' => 'LibreNMS\Authentication\RadiusAuthorizer',
                'http-auth' => 'LibreNMS\Authentication\HttpAuthAuthorizer',
                'ad-authorization' => 'LibreNMS\Authentication\ADAuthorizationAuthorizer',
                'ldap-authorization' => 'LibreNMS\Authentication\LdapAuthorizationAuthorizer',
            );

            if (!isset($configToClassMap[Config::get('auth_mechanism')])) {
                throw new \RuntimeException(Config::get('auth_mechanism') . ' not found as auth_mechanism');
            }

            static::$_instance = new $configToClassMap[Config::get('auth_mechanism')]();
        }
        return static::$_instance;
    }
}
