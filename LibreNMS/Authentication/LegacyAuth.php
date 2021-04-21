<?php

namespace LibreNMS\Authentication;

use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use LibreNMS\Interfaces\Authentication\Authorizer;

class LegacyAuth
{
    protected static $_instance;
    private static $configToClassMap = [
        'mysql' => 'LibreNMS\Authentication\MysqlAuthorizer',
        'active_directory' => 'LibreNMS\Authentication\ActiveDirectoryAuthorizer',
        'ldap' => 'LibreNMS\Authentication\LdapAuthorizer',
        'radius' => 'LibreNMS\Authentication\RadiusAuthorizer',
        'http-auth' => 'LibreNMS\Authentication\HttpAuthAuthorizer',
        'ad-authorization' => 'LibreNMS\Authentication\ADAuthorizationAuthorizer',
        'ldap-authorization' => 'LibreNMS\Authentication\LdapAuthorizationAuthorizer',
        'sso' => 'LibreNMS\Authentication\SSOAuthorizer',
    ];

    /**
     * Gets the authorizer based on the config
     *
     * @return Authorizer
     */
    public static function get()
    {
        if (! static::$_instance) {
            $class = self::getClass();
            static::$_instance = new $class;
        }

        return static::$_instance;
    }

    /**
     * The auth mechanism type.
     *
     * @return mixed
     */
    public static function getType()
    {
        return Config::get('auth_mechanism');
    }

    /**
     * Get class for the given or current authentication type/mechanism
     *
     * @param string $type
     * @return string
     */
    public static function getClass($type = null)
    {
        if (is_null($type)) {
            $type = self::getType();
        }

        if (! isset(self::$configToClassMap[$type])) {
            throw new \RuntimeException($type . ' not found as auth_mechanism');
        }

        return self::$configToClassMap[$type];
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
}
