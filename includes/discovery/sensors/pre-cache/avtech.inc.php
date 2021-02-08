<?php
/**
 * avtech.inc.php
 *
 * Grab all data under avtech enterprise oid and process it for yaml consumption
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

// table name => regex (first group is index, second group is id)
$virtual_tables = [
    'ra32-analog' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.1\.5\.((\d+)\.0)/',
    'ra32-relay' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.1\.6\.((\d+)\.0)/',
    'ra32-ext-temp' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.2\.((\d+)\.1\.0)/',
    'ra32-switch' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.3\.((\d+)\.0)/',
    'ra32-wish-temp' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.4\.((\d+)\.4\.1\.2\.0)/',
];

$data = snmp_walk($device, '.1.3.6.1.4.1.20916.1', '-OQn');
foreach (explode(PHP_EOL, $data) as $line) {
    [$oid, $value] = explode(' = ', $line);

    $processed = false;
    foreach ($virtual_tables as $vt_name => $vt_regex) {
        if (preg_match($vt_regex, $oid, $matches)) {
            $index = $matches[1];
            $id = $matches[2];

            $pre_cache[$vt_name][$index] = ['value' => $value, 'id' => $id];

            $processed = true;
            break;  // skip rest
        }
    }

    if (! $processed) {
        $pre_cache[$oid] = [[$oid => $value]];
    }
}

unset($data);
