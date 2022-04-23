<?php

// easier to rewrite for Active Directory than to bash it into existing LDAP implementation

// disable certificate checking before connect if required

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Exceptions\LdapMissingException;

class ActiveDirectoryAuthorizer extends AuthorizerBase
{
    use ActiveDirectoryCommon;

    protected static $CAN_UPDATE_PASSWORDS = false;

    protected $ldap_connection;
    protected $is_bound = false; // this variable tracks if bind has been called so we don't call it multiple times

    public function authenticate($credentials)
    {
        $this->connect();

        if ($this->ldap_connection) {
            // bind with sAMAccountName instead of full LDAP DN
            if (! empty($credentials['username']) && ! empty($credentials['password']) && ldap_bind($this->ldap_connection, $credentials['username'] . '@' . Config::get('auth_ad_domain'), $credentials['password'])) {
                $this->is_bound = true;
                // group membership in one of the configured groups is required
                if (Config::get('auth_ad_require_groupmembership', true)) {
                    // cycle through defined groups, test for memberOf-ship
                    foreach (Config::get('auth_ad_groups', []) as $group => $level) {
                        if ($this->userInGroup($credentials['username'], $group)) {
                            return true;
                        }
                    }

                    // failed to find user
                    if (Config::get('auth_ad_debug', false)) {
                        throw new AuthenticationException('User is not in one of the required groups or user/group is outside the base dn');
                    }

                    throw new AuthenticationException();
                } else {
                    // group membership is not required and user is valid
                    return true;
                }
            }
        }

        if (empty($credentials['password'])) {
            throw new AuthenticationException('A password is required');
        } elseif (Config::get('auth_ad_debug', false)) {
            ldap_get_option($this->ldap_connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
            throw new AuthenticationException(ldap_error($this->ldap_connection) . '<br />' . $extended_error);
        }

        throw new AuthenticationException(ldap_error($this->ldap_connection));
    }

    protected function userInGroup($username, $groupname)
    {
        $connection = $this->getConnection();

        // check if user is member of the given group or nested groups
        $search_filter = "(&(objectClass=group)(cn=$groupname))";

        // get DN for auth_ad_group
        $search = ldap_search(
            $connection,
            Config::get('auth_ad_base_dn'),
            $search_filter,
            ['cn']
        );
        $result = ldap_get_entries($connection, $search);

        if ($result == false || $result['count'] !== 1) {
            if (Config::get('auth_ad_debug', false)) {
                if ($result == false) {
                    // FIXME: what went wrong?
                    throw new AuthenticationException("LDAP query failed for group '$groupname' using filter '$search_filter'");
                } elseif ($result['count'] == 0) {
                    throw new AuthenticationException("Failed to find group matching '$groupname' using filter '$search_filter'");
                } elseif ($result['count'] > 1) {
                    throw new AuthenticationException("Multiple groups returned for '$groupname' using filter '$search_filter'");
                }
            }

            throw new AuthenticationException();
        }

        // special character handling
        $group_dn = addcslashes($result[0]['dn'], '()#');

        $search = ldap_search(
            $connection,
            Config::get('auth_ad_base_dn'),
            // add 'LDAP_MATCHING_RULE_IN_CHAIN to the user filter to search for $username in nested $group_dn
            // limiting to "DN" for shorter array
            '(&' . $this->userFilter($username) . "(memberOf:1.2.840.113556.1.4.1941:=$group_dn))",
            ['DN']
        );
        $entries = ldap_get_entries($connection, $search);

        return $entries['count'] > 0;
    }

    public function userExists($username, $throw_exception = false)
    {
        $connection = $this->getConnection();

        $search = ldap_search(
            $connection,
            Config::get('auth_ad_base_dn'),
            $this->userFilter($username),
            ['samaccountname']
        );
        $entries = ldap_get_entries($connection, $search);

        if ($entries['count']) {
            return true;
        }

        return false;
    }

    public function getUserlevel($username)
    {
        $userlevel = 0;
        if (! Config::get('auth_ad_require_groupmembership', true)) {
            if (Config::get('auth_ad_global_read', false)) {
                $userlevel = 5;
            }
        }

        // cycle through defined groups, test for memberOf-ship
        foreach (Config::get('auth_ad_groups', []) as $group => $level) {
            try {
                if ($this->userInGroup($username, $group)) {
                    $userlevel = max($userlevel, $level['level']);
                }
            } catch (AuthenticationException $e) {
            }
        }

        return $userlevel;
    }

    public function getUserid($username)
    {
        $connection = $this->getConnection();

        $attributes = ['objectsid'];
        $search = ldap_search(
            $connection,
            Config::get('auth_ad_base_dn'),
            $this->userFilter($username),
            $attributes
        );
        $entries = ldap_get_entries($connection, $search);

        if ($entries['count']) {
            return $this->getUseridFromSid($this->sidFromLdap($entries[0]['objectsid'][0]));
        }

        return -1;
    }

    /**
     * Bind to AD with the bind user if available, otherwise anonymous bind
     */
    protected function init()
    {
        if ($this->ldap_connection) {
            return;
        }

        $this->connect();
        $this->bind();
    }

    protected function connect()
    {
        if ($this->ldap_connection) {
            // no need to re-connect
            return;
        }

        if (! function_exists('ldap_connect')) {
            throw new LdapMissingException();
        }

        if (Config::has('auth_ad_check_certificates') &&
            ! Config::get('auth_ad_check_certificates')) {
            putenv('LDAPTLS_REQCERT=never');
        }

        if (Config::has('auth_ad_check_certificates') && Config::get('auth_ad_debug')) {
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }

        $this->ldap_connection = @ldap_connect(Config::get('auth_ad_url'));

        // disable referrals and force ldap version to 3
        ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    }

    public function bind($credentials = [])
    {
        if (! $this->ldap_connection) {
            $this->connect();
        }

        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        if (Config::has('auth_ad_binduser') && Config::has('auth_ad_bindpassword')) {
            $username = Config::get('auth_ad_binduser');
            $password = Config::get('auth_ad_bindpassword');
        }
        $username .= '@' . Config::get('auth_ad_domain');

        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, Config::get('auth_ad_timeout', 5));
        $bind_result = ldap_bind($this->ldap_connection, $username, $password);
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout

        if ($bind_result) {
            return $bind_result;
        }

        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, Config::get('auth_ad_timeout', 5));
        ldap_bind($this->ldap_connection);
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
    }

    protected function getConnection()
    {
        $this->init(); // make sure connected and bound

        return $this->ldap_connection;
    }
}
