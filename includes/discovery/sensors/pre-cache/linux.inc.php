<?php
/**
 * linux.inc.php
 *
 * LibreNMS pre-cache discovery module for Linux
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

echo 'RaspberryPi ';
$pre_cache['raspberry_pi_sensors'] = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121', [], "NET-SNMP-EXTEND-MIB");
