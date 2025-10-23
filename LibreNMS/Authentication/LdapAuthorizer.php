<?php

namespace LibreNMS\Authentication;

use App\Facades\LibrenmsConfig;
use ErrorException;
use LDAP\Connection;
use LibreNMS\Enum\LegacyAuthLevel;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Exceptions\LdapMissingException;

class LdapAuthorizer extends AuthorizerBase
{
    protected ?Connection $ldap_connection = null;
    private $userloginname = '';

    public function authenticate($credentials)
    {
        $connection = $this->getLdapConnection(true);

        if (! empty($credentials['username'])) {
            $username = $credentials['username'];
            $this->userloginname = $username;
            if (LibrenmsConfig::get('auth_ldap_wildcard_ou', false)) {
                $this->setAuthLdapSuffixOu($username);
            }

            if (! empty($credentials['password']) && ldap_bind($connection, $this->getFullDn($username), $credentials['password'])) {
                // ldap_bind has done a bind with the user credentials. If binduser is configured, rebind with the auth_ldap_binduser
                // normal user has restricted right to search in ldap. auth_ldap_binduser has full search rights
                if ((LibrenmsConfig::has('auth_ldap_binduser') || LibrenmsConfig::has('auth_ldap_binddn')) && LibrenmsConfig::has('auth_ldap_bindpassword')) {
                    $this->bind();
                }

                if (LibrenmsConfig::get('auth_ldap_require_groupmembership') === false) {
                    // skip group check if the server does not support ldap_compare (hint: google gsuite ldap)
                    return true;
                }

                $ldap_groups = $this->getGroupList();
                if (empty($ldap_groups)) {
                    // no groups, don't check membership
                    return true;
                } else {
                    foreach ($ldap_groups as $ldap_group) {
                        if (LibrenmsConfig::get('auth_ldap_userdn') === true) {
                            $ldap_comparison = ldap_compare(
                                $connection,
                                $ldap_group,
                                LibrenmsConfig::get('auth_ldap_groupmemberattr', 'memberUid'),
                                $this->getFullDn($username)
                            );
                        } else {
                            $ldap_comparison = ldap_compare(
                                $connection,
                                $ldap_group,
                                LibrenmsConfig::get('auth_ldap_groupmemberattr', 'memberUid'),
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

            $filter = '(' . LibrenmsConfig::get('auth_ldap_prefix') . $username . ')';
            $search = ldap_search($connection, trim(LibrenmsConfig::get('auth_ldap_suffix'), ','), $filter);
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
            $groups = LibrenmsConfig::get('auth_ldap_groups');

            // Find all defined groups $username is in
            $group_names = array_keys($groups);
            $ldap_group_filter = '';
            foreach ($group_names as $group_name) {
                $ldap_group_filter .= '(cn=' . trim($group_name) . ')';
            }
            if (count($group_names) > 1) {
                $ldap_group_filter = "(|{$ldap_group_filter})";
            }
            if (LibrenmsConfig::get('auth_ldap_userdn') === true) {
                $filter = "(&{$ldap_group_filter}(" . trim(LibrenmsConfig::get('auth_ldap_groupmemberattr', 'memberUid')) . '=' . $this->getFullDn($username) . '))';
            } else {
                $filter = "(&{$ldap_group_filter}(" . trim(LibrenmsConfig::get('auth_ldap_groupmemberattr', 'memberUid')) . '=' . $this->getMembername($username) . '))';
            }
            $search = ldap_search($connection, LibrenmsConfig::get('auth_ldap_groupbase'), $filter);
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

            $filter = '(' . LibrenmsConfig::get('auth_ldap_prefix') . $username . ')';
            $search = ldap_search($connection, trim(LibrenmsConfig::get('auth_ldap_suffix'), ','), $filter);
            $entries = ldap_get_entries($connection, $search);

            if ($entries['count']) {
                $uid_attr = strtolower(LibrenmsConfig::get('auth_ldap_uid_attribute', 'uidnumber'));

                return $entries[0][$uid_attr][0];
            }
        } catch (AuthenticationException $e) {
            echo $e->getMessage() . PHP_EOL;
        }

        return -1;
    }

    public function getUser($user_id): false|array
    {
        $connection = $this->getLdapConnection();

        $filter = '(' . LibrenmsConfig::get('auth_ldap_prefix') . $this->userloginname . ')';
        if (LibrenmsConfig::get('auth_ldap_userlist_filter') != null) {
            $filter = '(' . LibrenmsConfig::get('auth_ldap_userlist_filter') . ')';
        }

        $search = ldap_search($connection, trim(LibrenmsConfig::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($connection, $search);
        if (! $entries) {
            return false;
        }

        foreach ($entries as $key => $entry) {
            if ($key == 'count') {
                continue;
            }

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
        $type = LibrenmsConfig::get('auth_ldap_groupmembertype');

        if ($type == 'fulldn') {
            return $this->getFullDn($username);
        }

        if ($type == 'puredn') {
            try {
                $connection = $this->getLdapConnection();
                $filter = '(' . LibrenmsConfig::get('auth_ldap_attr.uid') . '=' . $username . ')';
                $search = ldap_search($connection, LibrenmsConfig::get('auth_ldap_groupbase'), $filter);
                $entries = ldap_get_entries($connection, $search);

                return $entries[0]['dn'];
            } catch (AuthenticationException $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        return $username;
    }

    public function getGroupList(): array
    {
        $ldap_groups = [];

        $default_group = 'cn=groupname,ou=groups,dc=example,dc=com';  // in the documentation
        if (LibrenmsConfig::get('auth_ldap_group', $default_group) !== $default_group) {
            $ldap_groups[] = LibrenmsConfig::get('auth_ldap_group');
        }

        foreach (LibrenmsConfig::get('auth_ldap_groups') as $key => $value) {
            $ldap_groups[] = "cn=$key," . LibrenmsConfig::get('auth_ldap_groupbase');
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
    protected function getFullDn($username): string
    {
        if (LibrenmsConfig::get('auth_ldap_use_dn_autodiscovery')) {
            // if dn autodiscovery is on, lookup user object in ldap and find dn of object.
            $userobj = '';
            $userobj = $this->getUser($this->getUserid($username));
            if (! empty($userobj)) {
                return $userobj['dn'];
            }
        }

        // if dn autodiscovery is off or dn cannot be found in ldap response, construct dn from ldap prefix, username and ldap suffix. this is default.
        return LibrenmsConfig::get('auth_ldap_prefix', '') . $username . LibrenmsConfig::get('auth_ldap_suffix', '');
    }

    /**
     * Set auth_ldap_suffix ou according to $username dn
     * useful if LibrenmsConfig::get('auth_ldap_wildcard_ou) is set
     *
     * @internal
     *
     * @return false|true
     */
    protected function setAuthLdapSuffixOu($username): bool
    {
        $connection = $this->getLdapConnection();
        $filter = '(' . LibrenmsConfig::get('auth_ldap_attr.uid') . '=' . $username . ')';
        $base_dn = preg_replace('/,ou=[^,]+,/', ',', LibrenmsConfig::get('auth_ldap_suffix'));
        $base_dn = trim($base_dn, ',');
        $search = ldap_search($connection, $base_dn, $filter);
        $results = ldap_get_entries($connection, $search);
        if (! $results) {
            return false;
        }

        foreach ($results as $entry) {
            if (isset($entry['uid'][0]) && $entry['uid'][0] == $username) {
                preg_match('~,ou=([^,]+),~', $entry['dn'], $matches);
                $user_ou = $matches[1] ?? '';
                $new_auth_ldap_suffix = preg_replace('/,ou=[^,]+,/', ',ou=' . $user_ou . ',', LibrenmsConfig::get('auth_ldap_suffix'));
                LibrenmsConfig::set('auth_ldap_suffix', $new_auth_ldap_suffix);

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
     * @return Connection
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
    private function ldapToUser(array $entry): array
    {
        $uid_attr = strtolower(LibrenmsConfig::get('auth_ldap_uid_attribute', 'uidnumber'));

        return [
            'username' => $entry['uid'][0] ?? null,
            'realname' => $entry['cn'][0] ?? null,
            'user_id' => $entry[$uid_attr][0] ?? null,
            'dn' => $entry['dn'] ?? null,
            'email' => $entry[LibrenmsConfig::get('auth_ldap_emailattr', 'mail')][0] ?? null,
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

        $port = LibrenmsConfig::get('auth_ldap_port');
        $uri = LibrenmsConfig::get('auth_ldap_server');
        if ($port && ! str_contains($uri, '://')) {
            $scheme = $port == 636 ? 'ldaps://' : 'ldap://';
            $uri = $scheme . $uri . ':' . $port;
        }

        $this->ldap_connection = ldap_connect($uri);

        if (empty($this->ldap_connection)) {
            throw new AuthenticationException('Fatal error while connecting to LDAP server, uri not valid: ' . $uri);
        }

        ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, LibrenmsConfig::get('auth_ldap_version', 3));

        $use_tls = LibrenmsConfig::get('auth_ldap_starttls');
        if ($use_tls == 'optional' || $use_tls == 'required') {
            $tls_success = ldap_start_tls($this->ldap_connection);
            if ($use_tls == 'required' && $tls_success === false) {
                $error = ldap_error($this->ldap_connection);
                throw new AuthenticationException("Fatal error: LDAP TLS required but not successfully negotiated: $error");
            }
        }
    }

    public function bind($credentials = []): void
    {
        if (LibrenmsConfig::get('auth_ldap_debug')) {
            ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        }
        /*
         * Due to https://bugs.php.net/bug.php?id=78029 these set options are done at this stage otherwise they
         * will not take effect after the first bind is performed.
         */
        if (LibrenmsConfig::get('auth_ldap_cacertfile')) {
            ldap_set_option($this->ldap_connection, LDAP_OPT_X_TLS_CACERTFILE, LibrenmsConfig::get('auth_ldap_cacertfile'));
        }
        if (LibrenmsConfig::get('auth_ldap_ignorecert')) {
            ldap_set_option($this->ldap_connection, LDAP_OPT_X_TLS_REQUIRE_CERT, 0);
        }

        $this->connect();

        $username = $credentials['username'] ?? null;
        $password = $credentials['password'] ?? null;

        if ((LibrenmsConfig::has('auth_ldap_binduser') || LibrenmsConfig::has('auth_ldap_binddn')) && LibrenmsConfig::has('auth_ldap_bindpassword')) {
            if (LibrenmsConfig::get('auth_ldap_binddn') == null) {
                LibrenmsConfig::set('auth_ldap_binddn', $this->getFullDn(LibrenmsConfig::get('auth_ldap_binduser')));
            }
            $username = LibrenmsConfig::get('auth_ldap_binddn');
            $password = LibrenmsConfig::get('auth_ldap_bindpassword');
        } elseif (! empty($credentials['username'])) {
            $username = $this->getFullDn($credentials['username']);
        }

        // With specified bind user
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, LibrenmsConfig::get('auth_ldap_timeout', 5));
        $bind_result = ldap_bind($this->ldap_connection, $username, $password);
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout

        if (LibrenmsConfig::get('auth_ldap_debug')) {
            echo 'Bind result: ' . ldap_error($this->ldap_connection) . PHP_EOL;
        }

        if ($bind_result) {
            return;
        }

        // Anonymous
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, LibrenmsConfig::get('auth_ldap_timeout', 5));
        ldap_bind($this->ldap_connection);
        ldap_set_option($this->ldap_connection, LDAP_OPT_NETWORK_TIMEOUT, -1); // restore timeout

        if (LibrenmsConfig::get('auth_ldap_debug')) {
            echo 'Anonymous bind result: ' . ldap_error($this->ldap_connection) . PHP_EOL;
        }
    }
}
