<?php

namespace LibreNMS\Authentication;

use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;

class LdapAuthorizer extends AuthorizerBase
{
    protected $ldap_connection;

    public function authenticate($username, $password)
    {
        $connection = $this->getLdapConnection(true);

        if ($username) {
            if ($password && ldap_bind($connection, $this->getFullDn($username), $password)) {
                $ldap_groups = $this->getGroupList();
                if (empty($ldap_groups)) {
                    // no groups, don't check membership
                    return true;
                } else {
                    foreach ($ldap_groups as $ldap_group) {
                        $ldap_comparison = ldap_compare(
                            $connection,
                            $ldap_group,
                            Config::get('auth_ldap_groupmemberattr', 'memberUid'),
                            $this->getMembername($username)
                        );
                        if ($ldap_comparison === true) {
                            return true;
                        }
                    }
                }
            }

            if (!isset($password) || $password == '') {
                throw new AuthenticationException('A password is required');
            }

            throw new AuthenticationException(ldap_error($connection));
        }

        throw new AuthenticationException();
    }


    public function reauthenticate($sess_id, $token)
    {
        $sess_id = clean($sess_id);
        $token = clean($token);

        list($username, $hash) = explode('|', $token);

        if (!$this->userExists($username, true)) {
            throw new AuthenticationException();
        }

        return $this->checkRememberMe($sess_id, $token);
    }

    public function userExists($username, $throw_exception = false)
    {
        try {
            $connection = $this->getLdapConnection();

            $filter = '(' . Config::get('auth_ldap_prefix') . $username . ')';
            $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
            $entries = ldap_get_entries($connection, $search);
            if ($entries['count']) {
                return 1;
            }
        } catch (AuthenticationException $e) {
            if ($throw_exception) {
                throw $e;
            } else {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        return 0;
    }


    public function getUserlevel($username)
    {
        $userlevel = 0;

        try {
            $connection = $this->getLdapConnection();
            $groups = Config::get('auth_ldap_groups');

            // Find all defined groups $username is in
            $group_names = array_keys($groups);
            $ldap_group_filter = '';
            foreach ($group_names as $group_name) {
                $ldap_group_filter .= "(cn=" . trim($group_name) . ")";
            }
            if (count($group_names) > 1) {
                $ldap_group_filter = "(|{$ldap_group_filter})";
            }
            $filter = "(&{$ldap_group_filter}(" . trim(Config::get('auth_ldap_groupmemberattr', 'memberUid')) . "=" . $this->getMembername($username) . "))";
            $search = ldap_search($connection, Config::get('auth_ldap_groupbase'), $filter);
            $entries = ldap_get_entries($connection, $search);

            // Loop the list and find the highest level
            foreach ($entries as $entry) {
                $groupname = $entry['cn'][0];
                if ($groups[$groupname]['level'] > $userlevel) {
                    $userlevel = $groups[$groupname]['level'];
                }
            }
        } catch (AuthenticationException $e) {
            echo $e->getMessage() . PHP_EOL;
        }

        return $userlevel;
    }


    public function getUserid($username)
    {
        try {
            $connection = $this->getLdapConnection();

            $filter = '(' . Config::get('auth_ldap_prefix') . $username . ')';
            $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
            $entries = ldap_get_entries($connection, $search);

            if ($entries['count']) {
                $uid_attr = strtolower(Config::get('auth_ldap_uid_attribute', 'uidnumber'));
                return $entries[0][$uid_attr][0];
            }
        } catch (AuthenticationException $e) {
            echo $e->getMessage() . PHP_EOL;
        }

        return -1;
    }

    public function getUserlist()
    {
        $userlist = [];

        try {
            $connection = $this->getLdapConnection();

            $ldap_groups = $this->getGroupList();
            if (empty($ldap_groups)) {
                d_echo('No groups defined.  Cannot search for users.');
                return [];
            }

            $filter = '(' . Config::get('auth_ldap_prefix') . '*)';

            // build group filter
            $group_filter = '';
            foreach ($ldap_groups as $group) {
                $group_filter .= '(memberOf=' . trim($group) . ')';
            }
            if (count($ldap_groups) > 1) {
                $group_filter = "(|$group_filter)";
            }

            // search using memberOf
            $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), "(&$filter$group_filter)");
            if (ldap_count_entries($connection, $search)) {
                foreach (ldap_get_entries($connection, $search) as $entry) {
                    $user = $this->ldapToUser($entry);
                    $userlist[$user['username']] = $user;
                }
            } else {
                // probably doesn't support memberOf, go through all users, this could be slow
                $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
                foreach (ldap_get_entries($connection, $search) as $entry) {
                    foreach ($ldap_groups as $ldap_group) {
                        if (ldap_compare(
                            $connection,
                            $ldap_group,
                            Config::get('auth_ldap_groupmemberattr', 'memberUid'),
                            $this->getMembername($entry['uid'][0])
                        )) {
                            $user = $this->ldapToUser($entry);
                            $userlist[$user['username']] = $user;
                        }
                    }
                }
            }
        } catch (AuthenticationException $e) {
            echo $e->getMessage() . PHP_EOL;
        }

        return $userlist;
    }

    public function getUser($user_id)
    {
        foreach ($this->getUserlist() as $user) {
            if ($user['user_id'] === $user_id) {
                return $user;
            }
        }
        return 0;
    }

    protected function getMembername($username)
    {
        $type = Config::get('auth_ldap_groupmembertype');

        if ($type == 'fulldn') {
            return $this->getFullDn($username);
        }

        if ($type == 'puredn') {
            try {
                $connection = $this->getLdapConnection();
                $filter = '(' . Config::get('auth_ldap_attr.uid') . '=' . $username . ')';
                $search = ldap_search($connection, Config::get('auth_ldap_groupbase'), $filter);
                $entries = ldap_get_entries($connection, $search);
                return $entries[0]['dn'];
            } catch (AuthenticationException $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        return $username;
    }


    public function getGroupList()
    {
        $ldap_groups = array();

        $default_group = 'cn=groupname,ou=groups,dc=example,dc=com';  // in the documentation
        if (Config::get('auth_ldap_group', $default_group) !== $default_group) {
            $ldap_groups[] = Config::get('auth_ldap_group');
        }

        foreach (Config::get('auth_ldap_groups') as $key => $value) {
            $ldap_groups[] = "cn=$key,".Config::get('auth_ldap_groupbase');
        }

        return $ldap_groups;
    }

    /**
     * Get the full dn with auth_ldap_prefix and auth_ldap_suffix
     * @internal
     *
     * @return string
     */
    protected function getFullDn($username)
    {
        return Config::get('auth_ldap_prefix', '') . $username . Config::get('auth_ldap_suffix', '');
    }

    /**
     * Get the ldap connection. If it hasn't been established yet, connect and try to bind.
     * @internal
     *
     * @param bool $skip_bind do not attempt to bind on connection
     * @return false|resource
     * @throws AuthenticationException
     */
    protected function getLdapConnection($skip_bind = false)
    {

        if ($this->ldap_connection) {
            return $this->ldap_connection; // bind already attempted
        }

        if (!function_exists('ldap_connect')) {
            throw new AuthenticationException("PHP does not support LDAP, please install or enable the PHP LDAP extension.");
        }

        $this->ldap_connection = @ldap_connect(Config::get('auth_ldap_server'), Config::get('auth_ldap_port', 389));

        if (!$this->ldap_connection) {
            throw new AuthenticationException('Unable to connect to ldap server');
        }

        ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, Config::get('auth_ldap_version', 3));

        $use_tls = Config::get('auth_ldap_starttls');
        if ($use_tls == 'optional'||$use_tls == 'require') {
            $tls_success = ldap_start_tls($this->ldap_connection);
            if ($use_tls == 'require' && $tls_success === false) {
                $error = ldap_error($this->ldap_connection);
                throw new AuthenticationException("Fatal error: LDAP TLS required but not successfully negotiated: $error");
            }
        }

        if ($skip_bind) {
            return $this->ldap_connection;
        }

        if (Config::get('auth_ldap_debug')) {
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }

        // set timeout
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, Config::get('auth_ldap_timeout', 5));

        // With specified bind user
        if ((Config::has('auth_ldap_binduser') || Config::has('auth_ldap_binddn'))
            && Config::has('auth_ldap_bindpassword')
        ) {
            if (Config::has('auth_ldap_binddn')) {
                $bind_dn = Config::get('auth_ldap_binddn');
            } else {
                $bind_dn = $this->getFullDn(Config::get('auth_ldap_binduser'));
            }


            $bind_result = ldap_bind($this->ldap_connection, $bind_dn, Config::get('auth_ldap_bindpassword'));

            if (Config::get('auth_ldap_debug')) {
                echo "Bind result: " . ldap_error($this->ldap_connection) . PHP_EOL;
            }

            if ($bind_result) {
                ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
                return $this->ldap_connection;
            }
        }

        // Anonymous
        ldap_bind($this->ldap_connection);

        if (Config::get('auth_ldap_debug')) {
            echo "Anonymous bind result: " . ldap_error($this->ldap_connection) . PHP_EOL;
        }

        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout
        return $this->ldap_connection;
    }

    /**
     * @param array $entry ldap entry array
     * @return array
     */
    private function ldapToUser($entry)
    {
        $uid_attr = strtolower(Config::get('auth_ldap_uid_attribute', 'uidnumber'));
        return [
            'username' => $entry['uid'][0],
            'realname' => $entry['cn'][0],
            'user_id' => $entry[$uid_attr][0],
            'email' => $entry[Config::get('auth_ldap_emailattr', 'mail')][0],
            'level' => $this->getUserlevel($entry['uid'][0]),
        ];
    }
}
