<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Interfaces\Authentication\Authorizer;

class Auth
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
                'sso' => 'LibreNMS\Authentication\SSOAuthorizer',
            );

            $auth_mechanism = Config::get('auth_mechanism');
            if (!isset($configToClassMap[$auth_mechanism])) {
                throw new \RuntimeException($auth_mechanism . ' not found as auth_mechanism');
            }

            static::$_instance = new $configToClassMap[$auth_mechanism]();
        }
        return static::$_instance;
    }

    /**
     * Destroy the existing instance and get a new one - required for tests.
     *
     * @return Authorizer
     */
    public static function reset()
    {
        static::$_instance = null;
        return static::get();
    }

    public static function check()
    {
        return static::get()->sessionAuthenticated();
    }

    public static function user()
    {
        return new UserProxy;
    }
    
    public static function id()
    {
        return $_SESSION['user_id'];
    }
}
