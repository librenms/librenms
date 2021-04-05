<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Exceptions\LdapMissingException;

class ADAuthorizationAuthorizer extends MysqlAuthorizer
{
    use LdapSessionCache;
    use ActiveDirectoryCommon;

    protected static $AUTH_IS_EXTERNAL = true;
    protected static $CAN_UPDATE_PASSWORDS = false;

    protected $ldap_connection;

    public function __construct()
    {
        if (! function_exists('ldap_connect')) {
            throw new LdapMissingException();
        }

        // Disable certificate checking before connect if required
        if (Config::has('auth_ad_check_certificates') &&
            Config::get('auth_ad_check_certificates') == 0) {
            putenv('LDAPTLS_REQCERT=never');
        }

        // Set up connection to LDAP server
        $this->ldap_connection = @ldap_connect(Config::get('auth_ad_url'));
        if (! $this->ldap_connection) {
            throw new AuthenticationException('Fatal error while connecting to AD url ' . Config::get('auth_ad_url') . ': ' . ldap_error($this->ldap_connection));
        }

        // disable referrals and force ldap version to 3
        ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);

        // Bind to AD
        if (Config::has('auth_ad_binduser') && Config::has('auth_ad_bindpassword')) {
            // With specified bind user
            if (! ldap_bind($this->ldap_connection, Config::get('auth_ad_binduser') . '@' . Config::get('auth_ad_domain'), Config::get('auth_ad_bindpassword'))) {
                echo ldap_error($this->ldap_connection);
            }
        } else {
            // Anonymous
            if (! ldap_bind($this->ldap_connection)) {
                echo ldap_error($this->ldap_connection);
            }
        }
    }

    public function authenticate($credentials)
    {
        if (isset($credentials['username']) && $this->userExists($credentials['username'])) {
            return true;
        }

        if (Config::get('http_auth_guest')) {
            return true;
        }

        throw new AuthenticationException();
    }

    public function userExists($username, $throw_exception = false)
    {
        if ($this->authLdapSessionCacheGet('user_exists')) {
            return true;
        }

        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            $this->userFilter($username),
            ['samaccountname']
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            /*
             * Cache positiv result as this will result in more queries which we
             * want to speed up.
             */
            $this->authLdapSessionCacheSet('user_exists', 1);

            return true;
        }

        return false;
    }

    public function getUserlevel($username)
    {
        $userlevel = $this->authLdapSessionCacheGet('userlevel');
        if ($userlevel) {
            return $userlevel;
        } else {
            $userlevel = 0;
        }

        // Find all defined groups $username is in
        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            $this->userFilter($username),
            ['memberOf']
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        // Loop the list and find the highest level
        foreach ($entries[0]['memberof'] as $entry) {
            $group_cn = $this->getCn($entry);
            $auth_ad_groups = Config::get('auth_ad_groups');
            if ($auth_ad_groups[$group_cn]['level'] > $userlevel) {
                $userlevel = $auth_ad_groups[$group_cn]['level'];
            }
        }

        $this->authLdapSessionCacheSet('userlevel', $userlevel);

        return $userlevel;
    }

    public function getUserid($username)
    {
        $user_id = $this->authLdapSessionCacheGet('userid');
        if (isset($user_id)) {
            return $user_id;
        } else {
            $user_id = -1;
        }

        $attributes = ['objectsid'];
        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            $this->userFilter($username),
            $attributes
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            $user_id = $this->getUseridFromSid($this->sidFromLdap($entries[0]['objectsid'][0]));
        }

        $this->authLdapSessionCacheSet('userid', $user_id);

        return $user_id;
    }

    protected function getConnection()
    {
        return $this->ldap_connection;
    }
}
