<?php
/**
 * istars.inc.php
 *
 * LibreNMS os poller module for East iStars UPS
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

$temp_data = snmp_getnext_multi($device, 'upsIdentManufacturer upsIdentModel upsIdentUPSSoftwareVersion upsIdentAgentSoftwareVersion upsIdentAttachedDevices', '-OQUs', 'UPS-MIB');

$hardware = $temp_data['upsIdentManufacturer'] . $temp_data['upsIdentModel'];
$version  = $temp_data['upsIdentAgentSoftwareVersion'] . $temp_data['upsIdentUPSSoftwareVersion'];
$features = $temp_data['upsIdentAttachedDevices'];
unset($temp_data);
