<?php

// easier to rewrite for Active Directory than to bash it into existing LDAP implementation

// disable certificate checking before connect if required
namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

class ActiveDirectoryAuthorizer extends AuthorizerBase
{
    protected static $CAN_UPDATE_PASSWORDS = 0;

    protected $ldap_connection;
    protected $is_bound = false; // this variable tracks if bind has been called so we don't call it multiple times

    public function authenticate($username, $password)
    {
        $this->connect();

        if ($this->ldap_connection) {
            // bind with sAMAccountName instead of full LDAP DN
            if ($username && $password && ldap_bind($this->ldap_connection, $username . '@' . Config::get('auth_ad_domain'), $password)) {
                $this->is_bound = true;
                // group membership in one of the configured groups is required
                if (Config::get('auth_ad_require_groupmembership', true)) {
                    // cycle through defined groups, test for memberOf-ship
                    foreach (Config::get('auth_ad_groups', array()) as $group => $level) {
                        if ($this->userInGroup($username, $group)) {
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

        if (!isset($password) || $password == '') {
            throw new AuthenticationException('A password is required');
        } elseif (Config::get('auth_ad_debug', false)) {
            ldap_get_option($this->ldap_connection, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
            throw new AuthenticationException(ldap_error($this->ldap_connection).'<br />'.$extended_error);
        }

        throw new AuthenticationException(ldap_error($this->ldap_connection));
    }

    public function reauthenticate($sess_id, $token)
    {
        if ($this->bind(false, true)) {
            $sess_id = clean($sess_id);
            $token = clean($token);
            list($username, $hash) = explode('|', $token);

            if (!$this->userExists($username)) {
                if (Config::get('auth_ad_debug', false)) {
                    throw new AuthenticationException("$username is not a valid AD user");
                }
                throw new AuthenticationException();
            }

            return $this->checkRememberMe($sess_id, $token);
        }

        return false;
    }


    protected function userInGroup($username, $groupname)
    {
        // check if user is member of the given group or nested groups


        $search_filter = "(&(objectClass=group)(cn=$groupname))";

        // get DN for auth_ad_group
        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            $search_filter,
            array("cn")
        );
        $result = ldap_get_entries($this->ldap_connection, $search);

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

        $group_dn = $result[0]["dn"];

        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            // add 'LDAP_MATCHING_RULE_IN_CHAIN to the user filter to search for $username in nested $group_dn
            // limiting to "DN" for shorter array
            "(&" . static::userFilter($username) . "(memberOf:1.2.840.113556.1.4.1941:=$group_dn))",
            array("DN")
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        return ($entries["count"] > 0);
    }

    public function userExists($username, $throw_exception = false)
    {
        $this->bind(); // make sure we called bind

        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            static::userFilter($username),
            array('samaccountname')
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);


        if ($entries['count']) {
            return 1;
        }

        return 0;
    }


    public function getUserlevel($username)
    {
        $this->bind(); // make sure we called bind

        $userlevel = 0;
        if (!Config::get('auth_ad_require_groupmembership', true)) {
            if (Config::get('auth_ad_global_read', false)) {
                $userlevel = 5;
            }
        }

        // cycle through defined groups, test for memberOf-ship
        foreach (Config::get('auth_ad_groups', array()) as $group => $level) {
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
        $this->bind(); // make sure we called bind

        $attributes = array('objectsid');
        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            static::userFilter($username),
            $attributes
        );
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            return $this->getUseridFromSid($this->sidFromLdap($entries[0]['objectsid'][0]));
        }

        return -1;
    }

    protected function getDomainSid()
    {
        $this->bind(); // make sure we called bind

        // Extract only the domain components
        $dn_candidate = preg_replace('/^.*?DC=/i', 'DC=', Config::get('auth_ad_base_dn'));

        $search = ldap_read(
            $this->ldap_connection,
            $dn_candidate,
            '(objectClass=*)',
            array('objectsid')
        );
        $entry = ldap_get_entries($this->ldap_connection, $search);
        return substr($this->sidFromLdap($entry[0]['objectsid'][0]), 0, 41);
    }

    public function getUser($user_id)
    {
        $this->bind(); // make sure we called bind

        $domain_sid = $this->getDomainSid();

        $search_filter = "(&(objectcategory=person)(objectclass=user)(objectsid=$domain_sid-$user_id))";
        $attributes = array('samaccountname', 'displayname', 'objectsid', 'mail');
        $search = ldap_search($this->ldap_connection, Config::get('auth_ad_base_dn'), $search_filter, $attributes);
        $entry = ldap_get_entries($this->ldap_connection, $search);

        if (isset($entry[0]['samaccountname'][0])) {
            return $this->userFromAd($entry[0]);
        }

        return array();
    }

    public function deleteUser($userid)
    {
        dbDelete('bill_perms', '`user_id` =  ?', array($userid));
        dbDelete('devices_perms', '`user_id` =  ?', array($userid));
        dbDelete('ports_perms', '`user_id` =  ?', array($userid));
        dbDelete('users_prefs', '`user_id` =  ?', array($userid));
        return 0;
    }


    public function getUserlist()
    {
        $this->bind(); // make sure we called bind

        $userlist = array();
        $ldap_groups = $this->getGroupList();

        foreach ($ldap_groups as $ldap_group) {
            $search_filter = "(&(memberOf:1.2.840.113556.1.4.1941:=$ldap_group)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
            if (Config::get('auth_ad_user_filter')) {
                $search_filter = "(&" . Config::get('auth_ad_user_filter') . $search_filter .")";
            }
            $attributes = array('samaccountname', 'displayname', 'objectsid', 'mail');
            $search = ldap_search($this->ldap_connection, Config::get('auth_ad_base_dn'), $search_filter, $attributes);
            $results = ldap_get_entries($this->ldap_connection, $search);

            foreach ($results as $result) {
                if (isset($result['samaccountname'][0])) {
                    $userlist[$result['samaccountname'][0]] = $this->userFromAd($result);
                }
            }
        }

        return array_values($userlist);
    }

    /**
     * Generate a user array from an AD LDAP entry
     * Must have the attributes: objectsid, samaccountname, displayname, mail
     * @internal
     *
     * @param $entry
     * @return array
     */
    protected function userFromAd($entry)
    {
        return array(
            'user_id' => $this->getUseridFromSid($this->sidFromLdap($entry['objectsid'][0])),
            'username' => $entry['samaccountname'][0],
            'realname' => $entry['displayname'][0],
            'email' => isset($entry['mail'][0]) ? $entry['mail'][0] : null,
            'descr' => '',
            'level' => $this->getUserlevel($entry['samaccountname'][0]),
            'can_modify_passwd' => 0,
        );
    }

    protected function getEmail($username)
    {
        $this->bind(); // make sure we called bind

        $attributes = array('mail');
        $search = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            static::userFilter($username),
            $attributes
        );
        $result = ldap_get_entries($this->ldap_connection, $search);
        unset($result[0]['mail']['count']);
        return current($result[0]['mail']);
    }

    protected function getFullname($username)
    {
        $this->bind(); // make sure we called bind

        $attributes = array('name');
        $result = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            static::userFilter($username),
            $attributes
        );
        $entries = ldap_get_entries($this->ldap_connection, $result);
        if ($entries['count'] > 0) {
            $membername = $entries[0]['name'][0];
        } else {
            $membername = $username;
        }

        return $membername;
    }


    public function getGroupList()
    {
        $ldap_groups   = array();

        // show all Active Directory Users by default
        $default_group = 'Users';

        if (Config::has('auth_ad_group')) {
            if (Config::get('auth_ad_group') !== $default_group) {
                $ldap_groups[] = Config::get('auth_ad_group');
            }
        }

        if (!Config::has('auth_ad_groups') && !Config::has('auth_ad_group')) {
            $ldap_groups[] = $this->getDn($default_group);
        }

        foreach (Config::get('auth_ad_groups') as $key => $value) {
            $ldap_groups[] = $this->getDn($key);
        }

        return $ldap_groups;
    }

    protected function getDn($samaccountname)
    {
        $this->bind(); // make sure we called bind

        $attributes = array('dn');
        $result = ldap_search(
            $this->ldap_connection,
            Config::get('auth_ad_base_dn'),
            static::groupFilter($samaccountname),
            $attributes
        );
        $entries = ldap_get_entries($this->ldap_connection, $result);
        if ($entries['count'] > 0) {
            return $entries[0]['dn'];
        } else {
            return '';
        }
    }

    protected function getCn($dn)
    {
        $dn = str_replace('\\,', '~C0mmA~', $dn);
        preg_match('/[^,]*/', $dn, $matches, PREG_OFFSET_CAPTURE, 3);
        return str_replace('~C0mmA~', ',', $matches[0][0]);
    }

    protected function getUseridFromSid($sid)
    {
        return preg_replace('/.*-(\d+)$/', '$1', $sid);
    }

    protected function sidFromLdap($sid)
    {
            $sidUnpacked = unpack('H*hex', $sid);
            $sidHex = array_shift($sidUnpacked);
            $subAuths = unpack('H2/H2/n/N/V*', $sid);
            $revLevel = hexdec(substr($sidHex, 0, 2));
            $authIdent = hexdec(substr($sidHex, 4, 12));
            return 'S-'.$revLevel.'-'.$authIdent.'-'.implode('-', $subAuths);
    }

    /**
     * Bind to AD with the bind user if available, otherwise anonymous bind
     * @internal
     *
     * @param bool $allow_anonymous attempt anonymous bind if bind user isn't available
     * @param bool $force force rebind
     * @return bool success or failure
     */
    protected function bind($allow_anonymous = true, $force = false)
    {
        if ($this->is_bound && !$force) {
            return true; // bind already attempted
        }

        $this->connect();  // make sure we are connected

        // set timeout
        ldap_set_option(
            $this->ldap_connection,
            LDAP_OPT_NETWORK_TIMEOUT,
            Config::get('auth_ad_timeout', 5)
        );

        // With specified bind user
        if (Config::has('auth_ad_binduser') && Config::has('auth_ad_bindpassword')) {
            $this->is_bound = true;
            $bind = ldap_bind(
                $this->ldap_connection,
                Config::get('auth_ad_binduser') . '@' . Config::get('auth_ad_domain'),
                Config::get('auth_ad_bindpassword')
            );
            ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
            return $bind;
        }

        $bind = false;

        // Anonymous
        if ($allow_anonymous) {
            $this->is_bound = true;
            $bind = ldap_bind($this->ldap_connection);
        }

        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
        return $bind;
    }

    protected function connect()
    {
        if ($this->ldap_connection) {
            // no need to re-connect
            return;
        }

        if (!function_exists('ldap_connect')) {
            throw new AuthenticationException("PHP does not support LDAP, please install or enable the PHP LDAP extension.");
        }

        if (Config::has('auth_ad_check_certificates') &&
            !Config::get('auth_ad_check_certificates')) {
            putenv('LDAPTLS_REQCERT=never');
        };

        if (Config::has('auth_ad_check_certificates') && Config::get('auth_ad_debug')) {
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }

        $this->ldap_connection = @ldap_connect(Config::get('auth_ad_url'));

        // disable referrals and force ldap version to 3
        ldap_set_option($this->ldap_connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    }

    public static function userFilter($username)
    {
        // don't return disabled users
        $user_filter = "(&(samaccountname=$username)(!(useraccountcontrol:1.2.840.113556.1.4.803:=2))";

        $extra = Config::get('auth_ad_user_filter');
        if ($extra) {
            $user_filter .= $extra;
        }
        $user_filter .= ')';

        return $user_filter;
    }

    public static function groupFilter($groupname)
    {
        $group_filter = "(samaccountname=$groupname)";

        $extra = Config::get('auth_ad_group_filter');
        if ($extra) {
            $group_filter = "(&$extra$group_filter)";
        }

        return $group_filter;
    }
}
