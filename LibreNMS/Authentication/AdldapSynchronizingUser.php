<?php
/**
 * AdldapSynchronizingUser.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Authentication;

use Adldap\Laravel\Events\Synchronizing;
use LibreNMS\Config;

class AdldapSynchronizingUser
{
    public function handle(Synchronizing $event)
    {
        $user = $event->model;

        if (empty($user->auth_type)) {
            $user->auth_type = 'adldap2';
        }

        // Set user level
        if (empty($user->level)) {
            // get list of user's groups
            $groups = $event->user
                ->getGroups($fields = ['distinguishedname'], $recursive = true)
                ->flatten()
                ->map(function ($group) {
                    return strtolower($group);
                });

            // get the highest level of the groups the user is in
            $level = collect(Config::get('auth_ad_groups'))->filter(function ($data, $key) use ($groups) {
                return $groups->contains(strtolower($key));
            })->pluck('level')->max();

            // set group user level or the default level
            $user->level = $level ?? (Config::get('auth_ad_global_read') ? 5 : 1);
        }
    }
}
