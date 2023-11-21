<?php

namespace LibreNMS\Authentication;

use ErrorException;
use LibreNMS\Config;
use LibreNMS\Enum\LegacyAuthLevel;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Exceptions\LdapMissingException;

class LdapAuthorizer extends AuthorizerBase
{
    protected $ldap_connection;
    private $userloginname = '';

    public function authenticate($credentials)
    {
        $connection = $this->getLdapConnection(true);

        if (! empty($credentials['username'])) {
            $username = $credentials['username'];
            $this->userloginname = $username;
            if (Config::get('auth_ldap_wildcard_ou', false)) {
                $this->setAuthLdapSuffixOu($username);
            }

            if (! empty($credentials['password']) && ldap_bind($connection, $this->getFullDn($username), $credentials['password'])) {
                // ldap_bind has done a bind with the user credentials. If binduser is configured, rebind with the auth_ldap_binduser
                // normal user has restricted right to search in ldap. auth_ldap_binduser has full search rights
                if ((Config::has('auth_ldap_binduser') || Config::has('auth_ldap_binddn')) && Config::has('auth_ldap_bindpassword')) {
                    $this->bind();
                }

                if (Config::get('auth_ldap_require_groupmembership') === false) {
                    // skip group check if the server does not support ldap_compare (hint: google gsuite ldap)
                    return true;
                }

                $ldap_groups = $this->getGroupList();
                if (empty($ldap_groups)) {
                    // no groups, don't check membership
                    return true;
                } else {
                    foreach ($ldap_groups as $ldap_group) {
                        if (Config::get('auth_ldap_userdn') === true) {
                            $ldap_comparison = ldap_compare(
                                $connection,
                                $ldap_group,
                                Config::get('auth_ldap_groupmemberattr', 'memberUid'),
                                $this->getFullDn($username)
                            );
                        } else {
                            $ldap_comparison = ldap_compare(
                                $connection,
                                $ldap_group,
                                Config::get('auth_ldap_groupmemberattr', 'memberUid'),
                                $this->getMembername($username)
                            );
                        }
                        if ($ldap_comparison === true) {
                            return true;
                        }
                    }
                }
            }

            if (empty($credentials['password'])) {
                throw new AuthenticationException('A password is required');
            }

            throw new AuthenticationException(ldap_error($connection));
        }

        throw new AuthenticationException();
    }

    public function userExists($username, $throw_exception = false)
    {
        try {
            $connection = $this->getLdapConnection();

            $filter = '(' . Config::get('auth_ldap_prefix') . $username . ')';
            $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
            $entries = ldap_get_entries($connection, $search);
            if ($entries['count']) {
                return true;
            }
        } catch (AuthenticationException $e) {
            if ($throw_exception) {
                throw $e;
            } else {
                echo $e->getMessage() . PHP_EOL;
            }
        } catch (ErrorException $e) {
            if ($throw_exception) {
                throw new AuthenticationException('Could not verify user', false, 0, $e);
            } else {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        return false;
    }

    public function getRoles(string $username): array|false
    {
        try {
            $connection = $this->getLdapConnection();
            $groups = Config::get('auth_ldap_groups');

            // Find all defined groups $username is in
            $group_names = array_keys($groups);
            $ldap_group_filter = '';
            foreach ($group_names as $group_name) {
                $ldap_group_filter .= '(cn=' . trim($group_name) . ')';
            }
            if (count($group_names) > 1) {
                $ldap_group_filter = "(|{$ldap_group_filter})";
            }
            if (Config::get('auth_ldap_userdn') === true) {
                $filter = "(&{$ldap_group_filter}(" . trim(Config::get('auth_ldap_groupmemberattr', 'memberUid')) . '=' . $this->getFullDn($username) . '))';
            } else {
                $filter = "(&{$ldap_group_filter}(" . trim(Config::get('auth_ldap_groupmemberattr', 'memberUid')) . '=' . $this->getMembername($username) . '))';
            }
            $search = ldap_search($connection, Config::get('auth_ldap_groupbase'), $filter);
            $entries = ldap_get_entries($connection, $search);

            $roles = [];
            // Collect all assigned roles
            foreach ($entries as $entry) {
                if (isset($entry['cn'][0])) {
                    $groupname = $entry['cn'][0];

                    if (isset($groups[$groupname]['roles']) && is_array($groups[$groupname]['roles'])) {
                        $roles = array_merge($roles, $groups[$groupname]['roles']);
                    } elseif (isset($groups[$groupname]['level'])) {
                        $role = LegacyAuthLevel::tryFrom($groups[$groupname]['level'])?->getName();
                        if ($role) {
                            $roles[] = $role;
                        }
                    }
                }
            }

            return array_unique($roles);
        } catch (AuthenticationException $e) {
            echo $e->getMessage() . PHP_EOL;
        }

        return false;
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

    public function getUser($user_id)
    {
        $connection = $this->getLdapConnection();

        $filter = '(' . Config::get('auth_ldap_prefix') . $this->userloginname . ')';
        if (Config::get('auth_ldap_userlist_filter') != null) {
            $filter = '(' . Config::get('auth_ldap_userlist_filter') . ')';
        }

        $search = ldap_search($connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($connection, $search);
        foreach ($entries as $entry) {
            $user = $this->ldapToUser($entry);
            if ($user['user_id'] != $user_id) {
                continue;
            }

            return $user;
        }

        return false;
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
        $ldap_groups = [];

        $default_group = 'cn=groupname,ou=groups,dc=example,dc=com';  // in the documentation
        if (Config::get('auth_ldap_group', $default_group) !== $default_group) {
            $ldap_groups[] = Config::get('auth_ldap_group');
        }

        foreach (Config::get('auth_ldap_groups') as $key => $value) {
            $ldap_groups[] = "cn=$key," . Config::get('auth_ldap_groupbase');
        }

        return $ldap_groups;
    }

    /**
     * Get the full dn with auth_ldap_prefix and auth_ldap_suffix
     *
     * @internal
     *
     * @return string
     */
    protected function getFullDn($username)
    {
        return Config::get('auth_ldap_prefix', '') . $username . Config::get('auth_ldap_suffix', '');
    }

    /**
     * Set auth_ldap_suffix ou according to $username dn
     * useful if Config::get('auth_ldap_wildcard_ou) is set
     *
     * @internal
     *
     * @return false|true
     */
    protected function setAuthLdapSuffixOu($username)
    {
        $connection = $this->getLdapConnection();
        $filter = '(' . Config::get('auth_ldap_attr.uid') . '=' . $username . ')';
        $base_dn = preg_replace('/,ou=[^,]+,/', ',', Config::get('auth_ldap_suffix'));
        $base_dn = trim($base_dn, ',');
        $search = ldap_search($connection, $base_dn, $filter);
        foreach (ldap_get_entries($connection, $search) as $entry) {
            if ($entry['uid'][0] == $username) {
                preg_match('~,ou=([^,]+),~', $entry['dn'], $matches);
                $user_ou = $matches[1];
                $new_auth_ldap_suffix = preg_replace('/,ou=[^,]+,/', ',ou=' . $user_ou . ',', Config::get('auth_ldap_suffix'));
                Config::set('auth_ldap_suffix', $new_auth_ldap_suffix);

                return true;
            }
        }

        return false;
    }

    /**
     * Get the ldap connection. If it hasn't been established yet, connect and try to bind.
     *
     * @internal
     *
     * @param  bool  $skip_bind  do not attempt to bind on connection
     * @return \LDAP\Connection
     *
     * @throws AuthenticationException
     */
    protected function getLdapConnection($skip_bind = false)
    {
        if ($this->ldap_connection) {
            return $this->ldap_connection; // bind already attempted
        }

        if ($skip_bind) {
            $this->connect();
        } else {
            $this->bind();
        }

        return $this->ldap_connection;
    }

    /**
     * @param  array  $entry  ldap entry array
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
        ];
    }

    private function connect(): void
    {
        if ($this->ldap_connection) {
            return;
        }

        if (! function_exists('ldap_connect')) {
            throw new LdapMissingException();
        }

        $this->ldap_connection = @ldap_connect(Config::get('auth_ldap_server'), Config::get('auth_ldap_port', 389));

        if (! $this->ldap_connection) {
            throw new AuthenticationException('Unable to connect to ldap server');
        }

        ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, Config::get('auth_ldap_version', 3));

        $use_tls = Config::get('auth_ldap_starttls');
        if ($use_tls == 'optional' || $use_tls == 'required') {
            $tls_success = ldap_start_tls($this->ldap_connection);
            if ($use_tls == 'required' && $tls_success === false) {
                $error = ldap_error($this->ldap_connection);
                throw new AuthenticationException("Fatal error: LDAP TLS required but not successfully negotiated: $error");
            }
        }
    }

    public function bind($credentials = [])
    {
        if (Config::get('auth_ldap_debug')) {
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }
        /*
         * Due to https://bugs.php.net/bug.php?id=78029 these set options are done at this stage otherwise they
         * will not take effect after the first bind is performed.
         */
        if (Config::get('auth_ldap_cacertfile')) {
            ldap_set_option($this->ldap_connection, LDAP_OPT_X_TLS_CACERTFILE, Config::get('auth_ldap_cacertfile'));
        }
        if (Config::get('auth_ldap_ignorecert')) {
            ldap_set_option($this->ldap_connection, LDAP_OPT_X_TLS_REQUIRE_CERT, 0);
        }

        $this->connect();

        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        if ((Config::has('auth_ldap_binduser') || Config::has('auth_ldap_binddn')) && Config::has('auth_ldap_bindpassword')) {
            if (Config::get('auth_ldap_binddn') == null) {
                Config::set('auth_ldap_binddn', $this->getFullDn(Config::get('auth_ldap_binduser')));
            }
            $username = Config::get('auth_ldap_binddn');
            $password = Config::get('auth_ldap_bindpassword');
        } elseif (! empty($credentials['username'])) {
            $username = $this->getFullDn($credentials['username']);
        }

        // With specified bind user
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, Config::get('auth_ldap_timeout', 5));
        $bind_result = ldap_bind($this->ldap_connection, $username, $password);
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout

        if (Config::get('auth_ldap_debug')) {
            echo 'Bind result: ' . ldap_error($this->ldap_connection) . PHP_EOL;
        }

        if ($bind_result) {
            return;
        }

        // Anonymous
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, Config::get('auth_ldap_timeout', 5));
        ldap_bind($this->ldap_connection);
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout

        if (Config::get('auth_ldap_debug')) {
            echo 'Anonymous bind result: ' . ldap_error($this->ldap_connection) . PHP_EOL;
        }
    }
}
