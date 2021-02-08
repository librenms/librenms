<?php
/*
 * Services.php
 *
 * Nagios services helper
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

class Services
{
    /**
     * List all available services from nagios plugins directory
     *
     * @return array
     */
    public static function list()
    {
        $services = [];
        if (is_dir(Config::get('nagios_plugins'))) {
            foreach (scandir(Config::get('nagios_plugins')) as $file) {
                if (substr($file, 0, 6) === 'check_') {
                    $services[] = substr($file, 6);
                }
            }
        }

        return $services;
    }
}
