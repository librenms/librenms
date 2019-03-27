<?php
/**
 * dell-net.inc.php
 *
 * LibreNMS os poller module for Dell-Networking
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
 * @copyright  2019 Spencer Butler
 * @author     Spencer Butler <github@crooked.app>
 */

$temp_data = snmpwalk_group($device, 'dellNetStackUnitTable', 'DELL-NETWORKING-CHASSIS-MIB');

    $hardware = snmp_get($device, 'entPhysicalDescr.4', '-Ovq', 'ENTITY-MIB');
    $version  = $temp_data[1]['dellNetStackUnitCodeVersion'];
    $features = $temp_data[1]['dellNetStackUnitServiceTag'] . '/' .  $temp_data[1]['dellNetStackUnitExpServiceCode'];
unset($temp_data);
