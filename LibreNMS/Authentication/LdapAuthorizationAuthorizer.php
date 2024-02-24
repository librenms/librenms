<?php
/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * libreNMS HTTP-Authentication and LDAP Authorization Library
 *
 * @author Maximilian Wilhelm <max@rfc2324.org>
 * @copyright 2016 LibreNMS, Barbarossa
 * @license GPL
 */

namespace LibreNMS\Authentication;

use App\Models\User;
use LibreNMS\Config;
use LibreNMS\Enum\LegacyAuthLevel;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Exceptions\LdapMissingException;

class LdapAuthorizationAuthorizer extends AuthorizerBase
{
    use LdapSessionCache;

    protected $ldap_connection;
    protected static $AUTH_IS_EXTERNAL = true;

    public function __construct()
    {
        if (! function_exists('ldap_connect')) {
            throw new LdapMissingException();
        }

        /**
         * Set up connection to LDAP server
         */
        $this->ldap_connection = @ldap_connect(Config::get('auth_ldap_server'), Config::get('auth_ldap_port'));
        if (! $this->ldap_connection) {
            throw new AuthenticationException('Fatal error while connecting to LDAP server, uri not valid: ' . Config::get('auth_ldap_server') . ':' . Config::get('auth_ldap_port'));
        }
        if (Config::get('auth_ldap_version')) {
            ldap_set_option($this->ldap_connection, LDAP_OPT_PROTOCOL_VERSION, Config::get('auth_ldap_version'));
        }

        if (Config::get('auth_ldap_starttls') && (Config::get('auth_ldap_starttls') == 'optional' || Config::get('auth_ldap_starttls') == 'required')) {
            $tls = ldap_start_tls($this->ldap_connection);
            if (Config::get('auth_ldap_starttls') == 'required' && $tls === false) {
                throw new AuthenticationException('Fatal error: LDAP TLS required but not successfully negotiated:' . ldap_error($this->ldap_connection));
            }
        }
        if ((Config::has('auth_ldap_binduser') || Config::has('auth_ldap_binddn')) && Config::has('auth_ldap_bindpassword')) {
            if (Config::get('auth_ldap_binddn') == null) {
                Config::set('auth_ldap_binddn', $this->getFullDn(Config::get('auth_ldap_binduser')));
            }
            $username = Config::get('auth_ldap_binddn');
            $password = Config::get('auth_ldap_bindpassword');
            $bind_result = ldap_bind($this->ldap_connection, $username, $password);
            if (! $bind_result) {
                throw new AuthenticationException('Fatal error: LDAP bind configured but not successfully authenticated:' . ldap_error($this->ldap_connection));
            }
        }
    }

    public function authenticate($credentials)
    {
        if (isset($credentials['username']) && $this->userExists($credentials['username'])) {
            return true;
        }

        $guest = Config::get('http_auth_guest');
        if ($guest && User::thisAuth()->where('username', $guest)->exists()) {
            return true;
        }

        throw new AuthenticationException();
    }

    public function userExists($username, $throw_exception = false)
    {
        if ($this->authLdapSessionCacheGet('user_exists')) {
            return true;
        }

        $filter = '(' . Config::get('auth_ldap_prefix') . $username . ')';
        $search = ldap_search($this->ldap_connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);
        if ($entries['count']) {
            /*
         * Cache positiv result as this will result in more queries which we
         * want to speed up.
         */
            $this->authLdapSessionCacheSet('user_exists', 1);

            return true;
        }

        /*
         * Don't cache that user doesn't exists as this might be a misconfiguration
         * on some end and the user will be happy if it "just works" after the user
         * has been added to LDAP.
         */
        return false;
    }

    public function getRoles(string $username): array|false
    {
        $roles = $this->authLdapSessionCacheGet('roles');
        if ($roles !== null) {
            return $roles;
        }
        $roles = [];

        // Find all defined groups $username is in
        $filter = '(&(|(cn=' . implode(')(cn=', array_keys(Config::get('auth_ldap_groups'))) . '))(' . Config::get('auth_ldap_groupmemberattr') . '=' . $this->getMembername($username) . '))';
        $search = ldap_search($this->ldap_connection, Config::get('auth_ldap_groupbase'), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);

        $authLdapGroups = Config::get('auth_ldap_groups');
        // Collect all roles
        foreach ($entries as $entry) {
            if (isset($entry['cn'][0])) {
                $groupname = $entry['cn'][0];

                if (isset($authLdapGroups[$groupname]['roles']) && is_array($authLdapGroups[$groupname]['roles'])) {
                    $roles = array_merge($roles, $authLdapGroups[$groupname]['roles']);
                } elseif (isset($authLdapGroups[$groupname]['level'])) {
                    $role = LegacyAuthLevel::tryFrom($authLdapGroups[$groupname]['level'])?->getName();
                    if ($role) {
                        $roles[] = $role;
                    }
                }
            }
        }

        $roles = array_unique($roles);
        $this->authLdapSessionCacheSet('roles', $roles);

        return $roles;
    }

    public function getUserid($username)
    {
        $user_id = $this->authLdapSessionCacheGet('userid');
        if (isset($user_id)) {
            return $user_id;
        }

        $guest_username = Config::get('http_auth_guest');
        $user_id = User::thisAuth()->where('username', $guest_username)->value('auth_id') ?: -1;

        $filter = '(' . Config::get('auth_ldap_prefix') . $username . ')';
        $search = ldap_search($this->ldap_connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            $user_id = $entries[0]['uidnumber'][0];
        }

        if ($user_id === -1) {
            // no user or guest user, don't allow
            if ($guest_username) {
                throw new AuthenticationException();
            } else {
                throw new AuthenticationException('Guest login allowed.');
            }
        }

        $this->authLdapSessionCacheSet('userid', $user_id);

        return $user_id;
    }

    public function getUser($user_id)
    {
        $uid_attr = strtolower(Config::get('auth_ldap_uid_attribute', 'uidnumber'));
        $filter = "($uid_attr=$user_id)";
        $search = ldap_search($this->ldap_connection, trim(Config::get('auth_ldap_suffix'), ','), $filter);
        $entries = ldap_get_entries($this->ldap_connection, $search);

        if ($entries['count']) {
            $entry = $entries[0];
            $username = $entry['uid'][0];
            $realname = $entry['cn'][0];
            $user_id = $entry['uidnumber'][0];
            $email = $entry[Config::get('auth_ldap_emailattr')][0];
            $ldap_groups = $this->getGroupList();
            foreach ($ldap_groups as $ldap_group) {
                $ldap_comparison = ldap_compare(
                    $this->ldap_connection,
                    $ldap_group,
                    Config::get('auth_ldap_groupmemberattr'),
                    $this->getMembername($username)
                );
                if (! Config::has('auth_ldap_group') || $ldap_comparison === true) {
                    return [
                        'username' => $username,
                        'realname' => $realname,
                        'user_id' => $user_id,
                        'email' => $email,
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Get the full dn with auth_ldap_prefix and auth_ldap_suffix
     *
     * @internal
     *
     * @return string
     */
    protected function getFullDn(string $username)
    {
        return Config::get('auth_ldap_prefix', '') . $username . Config::get('auth_ldap_suffix', '');
    }

    protected function getMembername($username)
    {
        if (Config::get('auth_ldap_groupmembertype') == 'fulldn') {
            $membername = Config::get('auth_ldap_prefix') . $username . Config::get('auth_ldap_suffix');
        } elseif (Config::get('auth_ldap_groupmembertype') == 'puredn') {
            $filter = '(' . Config::get('auth_ldap_attr.uid') . '=' . $username . ')';
            $search = ldap_search($this->ldap_connection, Config::get('auth_ldap_groupbase'), $filter);
            $entries = ldap_get_entries($this->ldap_connection, $search);
            $membername = $entries[0]['dn'];
        } else {
            $membername = $username;
        }

        return $membername;
    }

    public function getGroupList()
    {
        $ldap_groups = [];
        $default_group = 'cn=groupname,ou=groups,dc=example,dc=com';
        if (Config::has('auth_ldap_group')) {
            if (Config::get('auth_ldap_group') !== $default_group) {
                $ldap_groups[] = Config::get('auth_ldap_group');
            }
        }

        foreach (Config::get('auth_ldap_groups') as $key => $value) {
            $dn = "cn=$key," . Config::get('auth_ldap_groupbase');
            $ldap_groups[] = $dn;
        }

        return $ldap_groups;
    }
}
