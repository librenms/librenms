<?php

namespace LibreNMS\Authentication;

use ErrorException;
use Illuminate\Support\Facades\Log;
use LDAP\Connection;
use LibreNMS\Config;
use LibreNMS\Enum\LegacyAuthLevel;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Exceptions\LdapMissingException;

class LdapAuthorizer extends AuthorizerBase
{
    protected ?Connection $ldap_connection = null;
    private string $userLoginName = '';
    protected ?bool $ldapSearchable = null;

    public function authenticate($credentials)
    {
        $connection = $this->getLdapConnection(true);

        if (! empty($credentials['username'])) {
            $username = $credentials['username'];
            $this->userLoginName = $username;
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

                // check for group membership if required
                if ($this->userInAnyGroup($username, $this->getGroupList())) {
                    return true;
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

            if (empty($groups)) {
                return [];
            }

            // Find all defined groups $username is in
            $group_names = array_keys($groups);
            $ldap_group_filter = '';
            foreach ($group_names as $group_name) {
                $ldap_group_filter .= '(cn=' . trim($group_name) . ')';
            }
            if (count($group_names) > 1) {
                $ldap_group_filter = "(|{$ldap_group_filter})";
            }

            $user_dn = $this->getUserDn($username);
            $filter = "(&{$ldap_group_filter}(" . trim(Config::get('auth_ldap_groupmemberattr', 'memberUid')) . '=' . $user_dn . '))';
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

        $filter = '(' . Config::get('auth_ldap_prefix') . $this->userLoginName . ')';
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

    protected function getUserDn(string $username): string
    {
        return Config::get('auth_ldap_userdn') === true
            ? $this->getFullDn($username)
            : $this->getMembername($username);
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

    /**
     * Get the full dn with auth_ldap_prefix and auth_ldap_suffix
     *
     * @internal
     *
     * @return string
     */
    protected function getFullDn($username): string
    {
        return Config::get('auth_ldap_prefix', '') . $username . Config::get('auth_ldap_suffix', '');
    }

    protected function getBaseDn(): string
    {
        $suffix = Config::get('auth_ldap_suffix');
        $base_dn = preg_replace('/,ou=[^,]+,/', ',', $suffix);

        return trim($base_dn, ',');
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
        if (! $this->hasLdapSearchPermission()) {
            return false;
        }

        $connection = $this->getLdapConnection();
        $filter = '(' . Config::get('auth_ldap_attr.uid') . '=' . $username . ')';
        $base_dn = $this->getBaseDn();
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

        $port = Config::get('auth_ldap_port');
        $uri = Config::get('auth_ldap_server');
        if ($port && ! str_contains($uri, '://')) {
            $scheme = $port == 636 ? 'ldaps://' : 'ldap://';
            $uri = $scheme . $uri . ':' . $port;
        }

        $this->ldap_connection = ldap_connect($uri);

        if (empty($this->ldap_connection)) {
            throw new AuthenticationException('Fatal error while connecting to LDAP server, uri not valid: ' . $uri);
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

    protected function userInAnyGroup(mixed $username, array $ldap_groups): bool
    {
        if (empty($ldap_groups)) {
            return true;
        }

        return $this->hasLdapSearchPermission()
            ? $this->isUserInAnyGroupViaSearch($username, $ldap_groups)
            : $this->isUserInAnyGroupViaReadLoop($username, $ldap_groups);
    }

    protected function isUserInAnyGroupViaSearch(string $username, array $ldapGroups): bool
    {
        $connection = $this->getLdapConnection();
        $memberAttr = Config::get('auth_ldap_groupmemberattr', 'memberUid');
        $memberValue = $this->getUserDn($username);

        $escapedValue = ldap_escape($memberValue, '', LDAP_ESCAPE_FILTER);
        $escapedAttr = ldap_escape($memberAttr, '', LDAP_ESCAPE_FILTER);

        // Build OR filter for group DNs if groups are provided
        $groupFilters = array_map(
            fn ($dn) => sprintf('(distinguishedName=%s)', ldap_escape($dn, '', LDAP_ESCAPE_FILTER)),
            $ldapGroups
        );
        $groupFilter = count($groupFilters) > 1
            ? '(|' . implode('', $groupFilters) . ')'
            : ($groupFilters[0] ?? '');

        // Final filter: check if any group has the user as a member
        $filter = sprintf('(&(%s=%s)%s)', $escapedAttr, $escapedValue, $groupFilter);

        $baseDn = Config::get('auth_ldap_groups_base_dn', $this->getBaseDn());

        $search = ldap_search($connection, $baseDn, $filter, ['dn']);

        if (! $search) {
            Log::error('LDAP group search failed.', ['filter' => $filter]);

            return false;
        }

        $results = ldap_get_entries($connection, $search);

        return ((int) ($results['count'] ?? 0)) > 0;
    }

    protected function isUserInAnyGroupViaReadLoop(string $username, array $ldapGroups): bool
    {
        $connection = $this->getLdapConnection();
        $memberAttr = Config::get('auth_ldap_groupmemberattr', 'memberUid');
        $memberValue = $this->getUserDn($username);

        foreach ($ldapGroups as $groupDn) {
            // Read only the needed attribute from the group DN
            $read = ldap_read($connection, $groupDn, '(objectClass=*)', [$memberAttr]);
            $entries = ldap_get_entries($connection, $read);

            if (($entries['count'] ?? 0) === 0) {
                continue;
            }

            $attr = strtolower($memberAttr);
            $values = $entries[0][$attr] ?? null;

            if (! is_array($values) || ! isset($values['count'])) {
                continue;
            }

            if (in_array($memberValue, array_slice($values, 0, $values['count']), true)) {
                return true;
            }
        }

        return false;
    }

    protected function hasLdapSearchPermission(): bool
    {
        if ($this->ldapSearchable === null) {
            $connection = $this->getLdapConnection();
            $baseDn = $this->getBaseDn();
            $test = @ldap_search($connection, $baseDn, '(objectClass=*)', ['dn'], 0, 1);

            if ($test === false) {
                $error = ldap_error($connection);

                Log::debug('LDAP search test failed.', ['error' => $error]);
            }

            ldap_free_result($test);

            $this->ldapSearchable = $test !== false;
        }

        return $this->ldapSearchable;
    }
}
