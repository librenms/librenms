<?php
/**
 * sitemonitor.inc.php
 *
 * LibreNMS current discovery module for Packetflux SiteMonitor
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
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$oid = '.1.3.6.1.4.1.32050.2.1.27.5.4';
$current = (snmp_get($device, $oid, '-Oqv') / 10);
if ($current > 0) {
    discover_sensor($valid['sensor'], 'current', $device, $oid, 0, 'sitemonitor', 'Current', 10, 1, null, null, null, null, $current);
}
