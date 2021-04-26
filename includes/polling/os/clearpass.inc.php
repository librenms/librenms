<?php
/**
 * clearpass.inc.php
 *
 * LibreNMS os poller module for Aruba Clearpass
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$hardware_oid = '.1.3.6.1.4.1.14823.1.6.1.1.1.1.1.1.0';
$serial_oid = '.1.3.6.1.4.1.14823.1.6.1.1.1.1.1.2.0';
$firmware_oid = '.1.3.6.1.4.1.14823.1.6.1.1.1.1.1.3.0';
$clearpass_data = snmp_get_multi_oid($device, "$hardware_oid $serial_oid $firmware_oid");

$hardware = trim($clearpass_data[$hardware_oid], '"');
$serial = trim($clearpass_data[$serial_oid], '"');
$version = trim($clearpass_data[$firmware_oid], '"');

unset(
    $clearpass_data,
    $hardware_oid,
    $firmware_oid
);
