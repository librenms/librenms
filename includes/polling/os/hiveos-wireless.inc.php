<?php
/**
 * hiveos-wireless.inc.php
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
 * @copyright  2015 Søren Friis Rosiak
 * @author     sorenrosiak@gmail.com
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */

if (preg_match('/^(.+?),/', $device['sysDescr'], $hardware)) {
    $hardware = $store[1];
}
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.1.3.0', '-Ovq'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.1.13.0', '-Ovq'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.1.4.0', '-Ovq'), '"');

$apmodel = snmp_get($device, '.1.3.6.1.4.1.26928.1.2.6.0', '-OQv');
if ($apmodel == "AP250" or "AP550") {
    // AeroHive AH-SYSTEM-MIB is broken so calling the OID.
    $data = snmp_get_multi_oid($device, '.1.3.6.1.4.1.26928.1.2.5.0 .1.3.6.1.4.1.26928.1.2.6.0 .1.3.6.1.4.1.26928.1.2.12.0', '-OUQn');
    $hardware = isset($data['.1.3.6.1.4.1.26928.1.2.6.0']) ? $data['.1.3.6.1.4.1.26928.1.2.6.0'] : '';
    $version2 = isset($data['.1.3.6.1.4.1.26928.1.2.12.0']) ? $data['.1.3.6.1.4.1.26928.1.2.12.0'] : '';
    // Version has 'HiveOS ' included. We want to remove it so OS doesn't show HiveOS twice.
    $prefix = 'HiveOS ';
    $version = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $version2);
    $serial = isset($data['.1.3.6.1.4.1.26928.1.2.5.0']) ? $data['.1.3.6.1.4.1.26928.1.2.5.0'] : '';
}
