<?php
/**
 * Graph.php
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
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;
use LibreNMS\Config;

class Graph
{
    public static function getTypes()
    {
        return ['device', 'port', 'application', 'munin', 'service'];
    }

    /**
     * Get an array of all graph subtypes for the given type
     * @param string $type
     * @param Device $device
     * @return array
     */
    public static function getSubtypes($type, $device = null)
    {
        $types = [];

        // find the subtypes defined in files
        foreach (glob(base_path("/includes/html/graphs/$type/*.inc.php")) as $file) {
            $type = basename($file, '.inc.php');
            if ($type != 'auth') {
                $types[] = $type;
            }
        }

        if ($device != null) {
            // find the MIB subtypes
            $graphs = $device->graphs->pluck('graph');

            foreach (Config::get('graph_types') as $type => $type_data) {
                foreach (array_keys($type_data) as $subtype) {
                    if ($graphs->contains($subtype) && self::isMibGraph($type, $subtype)) {
                        $types[] = $subtype;
                    }
                }
            }
        }

        sort($types);

        return $types;
    }

    /**
     * Check if the given graph is a mib graph
     *
     * @param string $type
     * @param string $subtype
     * @return bool
     */
    public static function isMibGraph($type, $subtype)
    {
        return Config::get("graph_types.$type.$subtype.section") == 'mib';
    }

    public static function getOverviewGraphsForDevice($device)
    {
        if ($device->snmp_disable) {
            return Config::getOsSetting('ping', 'over');
        }

        if ($graphs = Config::getOsSetting($device->os, 'over')) {
            return $graphs;
        }

        $os_group = Config::getOsSetting($device->os, 'group');

        return Config::get("os_group.$os_group.over", Config::get('os.default.over'));
    }
}
