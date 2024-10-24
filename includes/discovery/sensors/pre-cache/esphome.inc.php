<?php
/**
 * esphome.inc.php
 *
 * Grab all data under esphome non enterprise oid and process it for yaml consumption
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
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

// table name => regex (first group is index, second group is id)
// $virtual_tables = [
//     'ra32-analog' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.1\.5\.((\d+)\.0)/',
//     'ra32-relay' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.1\.6\.((\d+)\.0)/',
//     'ra32-ext-temp' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.2\.((\d+)\.1\.0)/',
//     'ra32-switch' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.3\.((\d+)\.0)/',
//     'ra32-wish-temp' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.8\.1\.4\.((\d+)\.4\.1\.2\.0)/',
//     'ra32s-analog' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.1\.5\.((\d+)\.0)/',
//     'ra32s-relay' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.1\.6\.((\d+)\.0)/',
//     'ra32s-ext-temp' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.2\.((\d+)\.1\.0)/',
//     'ra32s-switch' => '/\.1\.3\.6\.1\.4\.1\.20916\.1\.11\.1\.3\.((\d+)\.0)/',
// ];

$data = trim(snmp_walk($device, '.1.3.9999', '-OQn')); #walk non enterprise OIDs (Wi-Fi,ESP32 heap,ESP8266 heap,Chip)
$data2 = trim(snmp_walk($device, '.1.3.6.1.2.1.1', '-OQn')); #walk System OID
$data3 = trim(snmp_walk($device, '.1.3.6.1.2.1.25', '-OQn')); #walk Storage OID

foreach (explode(PHP_EOL, $data) as $line) {
    [$oid, $value] = explode(' =', $line);
    $value = trim($value);
    $pre_cache[$oid] = [[$oid => $value]];
}

unset($data);

foreach (explode(PHP_EOL, $data2) as $line) {
    [$oid, $value] = explode(' =', $line);
    $value = trim($value);
    $pre_cache[$oid] = [[$oid => $value]];
}

unset($data2);

foreach (explode(PHP_EOL, $data3) as $line) {
    [$oid, $value] = explode(' =', $line);
    $value = trim($value);
    $pre_cache[$oid] = [[$oid => $value]];
}

unset($data3);