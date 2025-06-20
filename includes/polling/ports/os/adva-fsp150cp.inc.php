<?php

/**
 * adva-fsp150cp.inc.php
 *
 * LibreNMS ADVA port poller include
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
$advaports = SnmpQuery::walk([
    'FSP150-MIB::fsp150IfConfigUserString',
    'ENTITY-MIB::entPhysicalName',
])->table(1);

foreach ($advaports as $index => $entry) {
    // Indexes are the same as IfIndex and EntPhysicalIndex

    if (isset($port_stats[$index])) {
        if (isset($entry['FSP150-MIB::fsp150IfConfigUserString'])) {
            $port_stats[$index]['ifAlias'] = $entry['FSP150-MIB::fsp150IfConfigUserString'];
        }
        if (isset($entry['ENTITY-MIB::entPhysicalName'])) {
            $port_stats[$index]['ifName'] = $entry['ENTITY-MIB::entPhysicalName'];
        }
    }
}
