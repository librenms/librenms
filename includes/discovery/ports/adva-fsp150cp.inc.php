<?php
/**
 * adva-fsp150cp.inc.php
 *
 * LibreNMS ADVA port discovery
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
 */
$advaports = snmpwalk_cache_oid($device, 'fsp150IfConfigUserString', [], 'FSP150-MIB');
$advaports = snmpwalk_cache_oid($device, 'entPhysicalName', $advaports, 'ENTITY-MIB');

d_echo($advaports);

foreach ($advaports as $index => $entry) {
    // Indexes are the same as IfIndex and EntPhysicalIndex

    if (isset($port_stats[$index])) {
        $port_stats[$index]['ifAlias'] = $entry['fsp150IfConfigUserString'];
        $port_stats[$index]['ifName'] = $entry['entPhysicalName'];
    }
}
