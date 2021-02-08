<?php
/**
 * LdapSessionCache.php
 *
 * Session cache for ldap queries
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
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Authentication;

use Carbon\Carbon;
use LibreNMS\Config;
use Session;

trait LdapSessionCache
{
    protected function authLdapSessionCacheGet($attr)
    {
        $ttl = Config::get('auth_ldap_cache_ttl', 300);

        // no session, don't cache
        if (! class_exists('Session')) {
            return null;
        }

        // auth_ldap cache present in this session?
        if (! Session::has('auth_ldap')) {
            return null;
        }

        $cache = Session::get('auth_ldap');

        // $attr present in cache?
        if (! isset($cache[$attr])) {
            return null;
        }

        // Value still valid?
        if (time() - $cache[$attr]['last_updated'] >= $ttl) {
            return null;
        }

        return $cache[$attr]['value'];
    }

    protected function authLdapSessionCacheSet($attr, $value)
    {
        if (class_exists('Session')) {
            Session::put($attr, [
                'value' => $value,
                'last_updated' => Carbon::now(),
            ]);
        }
    }
}
