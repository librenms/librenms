<?php
/**
 * ethernetprobe2.inc.php
 *
 * LibreNMS sensors temp poller module for Atal EthernetProbe2
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$temp_type = snmp_get($device, '.1.3.6.1.4.1.3854.1.2.2.1.16.1.12.' . $sensor['sensor_index'], '-Oqv', 'SPAGENT-MIB');
$sensor_value = fahrenheit_to_celsius($sensor_value, $temp_type);
unset($temp_type);
