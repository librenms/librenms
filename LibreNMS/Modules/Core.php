<?php
/*
 * Core.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use Illuminate\Support\Str;
use LibreNMS\Config;

class Core
{
    /**
     * Detect the os of the given device.
     *
     * @param array $device device to check
     * @param bool $fetch fetch sysDescr and sysObjectID fresh from the device
     * @return string the name of the os
     * @throws \Exception
     */
    public static function detectOS($device, $fetch = true)
    {
        if ($fetch) {
            // some devices act odd when getting both oids at once
            $device['sysDescr'] = snmp_get($device, 'SNMPv2-MIB::sysDescr.0', '-Ovq');
            $device['sysObjectID'] = snmp_get($device, 'SNMPv2-MIB::sysObjectID.0', '-Ovqn');
        }

        d_echo("| {$device['sysDescr']} | {$device['sysObjectID']} | \n");

        $deferred_os = [];
        $generic_os = [
            'airos',
            'freebsd',
            'linux',
        ];

        // check yaml files
        $os_defs = Config::get('os');
        foreach ($os_defs as $os => $def) {
            if (isset($def['discovery']) && ! in_array($os, $generic_os)) {
                if (self::discoveryIsSlow($def)) {
                    // defer all os that use snmpget or snmpwalk
                    $deferred_os[] = $os;
                    continue;
                }

                foreach ($def['discovery'] as $item) {
                    if (self::checkDiscovery($device, $item, $def['mib_dir'] ?? null)) {
                        return $os;
                    }
                }
            }
        }

        // check deferred os
        $deferred_os = array_merge($deferred_os, $generic_os);
        foreach ($deferred_os as $os) {
            foreach ($os_defs[$os]['discovery'] as $item) {
                if (self::checkDiscovery($device, $item, $os_defs[$os]['mib_dir'] ?? null)) {
                    return $os;
                }
            }
        }

        return 'generic';
    }

    /**
     * Check an array of conditions if all match, return true
     * sysObjectID if sysObjectID starts with any of the values under this item
     * sysDescr if sysDescr contains any of the values under this item
     * sysDescr_regex if sysDescr matches any of the regexes under this item
     * snmpget perform an snmpget on `oid` and check if the result contains `value`. Other subkeys: options, mib, mibdir
     *
     * Appending _except to any condition will invert the match.
     *
     * @param array $device
     * @param array $array Array of items, keys should be sysObjectID, sysDescr, or sysDescr_regex
     * @param string|array $mibdir MIB directory for evaluated OS
     * @return bool the result (all items passed return true)
     */
    protected static function checkDiscovery($device, $array, $mibdir)
    {
        // all items must be true
        foreach ($array as $key => $value) {
            if ($check = Str::endsWith($key, '_except')) {
                $key = substr($key, 0, -7);
            }

            if ($key == 'sysObjectID') {
                if (Str::startsWith($device['sysObjectID'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysDescr') {
                if (Str::contains($device['sysDescr'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysDescr_regex') {
                if (preg_match_any($device['sysDescr'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'sysObjectID_regex') {
                if (preg_match_any($device['sysObjectID'], $value) == $check) {
                    return false;
                }
            } elseif ($key == 'snmpget') {
                $get_value = snmp_get(
                    $device,
                    $value['oid'],
                    $value['options'] ?? '-Oqv',
                    $value['mib'] ?? null,
                    $value['mib_dir'] ?? $mibdir
                );
                if (compare_var($get_value, $value['value'], $value['op'] ?? 'contains') == $check) {
                    return false;
                }
            } elseif ($key == 'snmpwalk') {
                $walk_value = snmp_walk(
                    $device,
                    $value['oid'],
                    $value['options'] ?? '-Oqv',
                    $value['mib'] ?? null,
                    $value['mib_dir'] ?? $mibdir
                );
                if (compare_var($walk_value, $value['value'], $value['op'] ?? 'contains') == $check) {
                    return false;
                }
            }
        }

        return true;
    }

    protected static function discoveryIsSlow($def)
    {
        foreach ($def['discovery'] as $item) {
            if (array_key_exists('snmpget', $item) || array_key_exists('snmpwalk', $item)) {
                return true;
            }
        }

        return false;
    }
}
