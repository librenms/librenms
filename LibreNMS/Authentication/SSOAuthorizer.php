<?php

/**
 * SSOAuthorizer.php
 *
 * -Description-
 *
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
 *
 * @link       https://librenms.org
 *
 * @copyright  2017 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

namespace LibreNMS\Authentication;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use Illuminate\Support\Arr;
use LibreNMS\Enum\LegacyAuthLevel;
use LibreNMS\Exceptions\AuthenticationException;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;

/**
 * Some functionality in this mechanism is inspired by confluence_http_authenticator (@chauth) and graylog-plugin-auth-sso (@Graylog)
 */
class SSOAuthorizer extends MysqlAuthorizer
{
    protected static $HAS_AUTH_USERMANAGEMENT = true;
    protected static $CAN_UPDATE_USER = true;
    protected static $CAN_UPDATE_PASSWORDS = false;
    protected static $AUTH_IS_EXTERNAL = true;

    public function authenticate($credentials)
    {
        if (empty($credentials['username'])) {
            throw new AuthenticationException('\'sso.user_attr\' config setting was not found or was empty');
        }

        // User has already been approved by the authenticator so if automatic user create/update is enabled, do it
        if (LibrenmsConfig::get('sso.create_users') || LibrenmsConfig::get('sso.update_users')) {
            $user = User::thisAuth()->firstOrNew(['username' => $credentials['username']]);

            $create = ! $user->exists && LibrenmsConfig::get('sso.create_users');
            $update = $user->exists && LibrenmsConfig::get('sso.update_users');

            if ($create || $update) {
                $user->auth_type = LegacyAuth::getType();
                $user->can_modify_passwd = 0;
                $user->realname = $this->authSSOGetAttr(LibrenmsConfig::get('sso.realname_attr'));
                $user->email = $this->authSSOGetAttr(LibrenmsConfig::get('sso.email_attr'));
                $user->descr = $this->authSSOGetAttr(LibrenmsConfig::get('sso.descr_attr')) ?: 'SSO User';
                $user->save();
            }
        }

        return true;
    }

    public function getExternalUsername()
    {
        return $this->authSSOGetAttr(LibrenmsConfig::get('sso.user_attr'), '');
    }

    /**
     * Return an attribute from the configured attribute store.
     * Returns null if the attribute cannot be found
     *
     * @param  string|null  $attr  The name of the attribute to find
     * @return string|null
     */
    public function authSSOGetAttr(?string $attr, string $prefix = 'HTTP_')
    {
        // Check attribute originates from a trusted proxy - we check it on every attribute just in case this gets called after initial login
        if ($this->authSSOProxyTrusted()) {
            // Short circuit everything if the attribute is non-existant or null
            if (empty($attr)) {
                return null;
            }

            $header_key = $prefix . str_replace('-', '_', strtoupper($attr));

            if (LibrenmsConfig::get('sso.mode') === 'header' && array_key_exists($header_key, $_SERVER)) {
                return $_SERVER[$header_key];
            } elseif (LibrenmsConfig::get('sso.mode') === 'env' && array_key_exists($attr, $_SERVER)) {
                return $_SERVER[$attr];
            } else {
                return null;
            }
        } else {
            throw new AuthenticationException('\'sso.trusted_proxies\'] is set in your config, but this connection did not originate from trusted source: ' . $_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * Checks to see if the connection originated from a trusted source address stored in the configuration.
     * Returns false if the connection is untrusted, true if the connection is trusted, and true if the trusted sources are not defined.
     *
     * @return bool
     */
    public function authSSOProxyTrusted()
    {
        // We assume IP is used - if anyone is using a non-ip transport, support will need to be added
        if (LibrenmsConfig::get('sso.trusted_proxies')) {
            try {
                // Where did the HTTP connection originate from?
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    // Do not replace this with a call to authSSOGetAttr
                    $source = IP::parse($_SERVER['REMOTE_ADDR']);
                } else {
                    return false;
                }

                $proxies = LibrenmsConfig::get('sso.trusted_proxies');

                if (is_array($proxies)) {
                    foreach ($proxies as $value) {
                        $proxy = IP::parse($value);

                        if ($source->inNetwork((string) $proxy)) {
                            // Proxy matches trusted subnet
                            return true;
                        }
                    }
                }

                // No match, proxy is untrusted
                return false;
            } catch (InvalidIpException) {
                // Webserver is talking nonsense (or, IPv10 has been deployed, or maybe something weird like a domain socket is in use?)
                return false;
            }
        }

        // Not enabled, trust everything
        return true;
    }

    /**
     * Calculate the roles to assign to a user based on the configuration and attributes supplied by the external authenticator.
     * Converts legacy levels into roles where required.
     *
     * @throws AuthenticationException
     */
    public function getRoles(string $username): array|false
    {
        if (LibrenmsConfig::get('sso.group_strategy') === 'attribute') {
            if (LibrenmsConfig::get('sso.level_attr')) {
                if (is_numeric($this->authSSOGetAttr(LibrenmsConfig::get('sso.level_attr')))) {
                    return Arr::wrap(LegacyAuthLevel::tryFrom((int) $this->authSSOGetAttr(LibrenmsConfig::get('sso.level_attr')))?->getName());
                } else {
                    throw new AuthenticationException('group assignment by attribute requested, but httpd is not setting the attribute to a number');
                }
            } else {
                throw new AuthenticationException('group assignment by attribute requested, but \'sso.level_attr\' not set in your config');
            }
        } elseif (LibrenmsConfig::get('sso.group_strategy') === 'map') {
            if (LibrenmsConfig::get('sso.group_level_map') && is_array(LibrenmsConfig::get('sso.group_level_map')) && LibrenmsConfig::get('sso.group_delimiter') && LibrenmsConfig::get('sso.group_attr')) {
                return $this->authSSOParseGroups();
            } else {
                throw new AuthenticationException('group assignment by level map requested, but \'sso.group_level_map\', \'sso.group_attr\', or \'sso.group_delimiter\' are not set in your config');
            }
        } elseif (LibrenmsConfig::get('sso.group_strategy') === 'static') {
            if (LibrenmsConfig::get('sso.static_level')) {
                return Arr::wrap(LegacyAuthLevel::tryFrom((int) LibrenmsConfig::get('sso.static_level'))?->getName());
            } else {
                throw new AuthenticationException('group assignment by static level was requested, but \'sso.static_level\' was not set in your config');
            }
        }

        throw new AuthenticationException('\'sso.group_strategy\' is not set to one of attribute, map, or static in your config - configuration is unsafe');
    }

    /**
     * Map a user to roles based on the configured table mapping.
     *
     * Supports the current RBAC mapping format:
     *     'NSS' => ['roles' => ['admin']]
     *
     * Legacy integer mappings are also supported and converted to role names.
     * If no group matches, sso.static_level (default 0) is used as a fallback.
     *
     * @return array<string>
     */
    public function authSSOParseGroups(): array
    {
        // Parse and normalize a delimited group list.
        $groups = explode(
            LibrenmsConfig::get('sso.group_delimiter', ';'),
            $this->authSSOGetAttr(LibrenmsConfig::get('sso.group_attr')) ?? ''
        );
        $groups = array_values(array_filter(array_map(trim(...), $groups), static fn ($group) => $group !== ''));

        // Only consider groups that match the filter expression - this is an optimisation for sites with thousands of groups.
        $group_filter = LibrenmsConfig::get('sso.group_filter');
        if ($group_filter) {
            $groups = array_values(array_filter($groups, static fn ($group) => preg_match($group_filter, (string) $group) === 1));
        }

        $config_map = LibrenmsConfig::get('sso.group_level_map', []);
        $roles = [];

        foreach ($groups as $group) {
            if (! array_key_exists($group, $config_map)) {
                continue;
            }

            $map = $config_map[$group];

            // Current RBAC mapping format.
            if (is_array($map) && isset($map['roles']) && is_array($map['roles'])) {
                $roles = array_merge($roles, $map['roles']);

                continue;
            }
        }

        // Preserve the previous static-level fallback when no configured group matches.
        if ($roles === []) {
            $static_role = LegacyAuthLevel::tryFrom((int) LibrenmsConfig::get('sso.static_level', 0))?->getName();
            if ($static_role !== null) {
                $roles[] = $static_role;
            }
        }

        return array_values(array_unique(array_filter($roles, static fn ($role) => is_string($role) && $role !== '')));
    }
}
