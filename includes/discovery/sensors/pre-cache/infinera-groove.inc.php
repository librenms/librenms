<?php
/**
 * infinera-groove.inc.php
 *
 * LibreNMS sensor pre-cache module for Infinera Groove
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
 * @copyright  2019 Nick Hilliard
 * @author     Nick Hilliard <nick@foobar.org>
 */
if (! is_array($pre_cache['infineragroove_portTable'])) {
    echo 'Caching OIDs:';
    $pre_cache['infineragroove_portTable'] = [];
    echo ' portTable';
    $pre_cache['infineragroove_portTable'] = snmpwalk_cache_multi_oid($device, 'portTable', $pre_cache['infineragroove_portTable'], 'CORIANT-GROOVE-MIB');
    echo ' OchOsTable';
    $pre_cache['infineragroove_portTable'] = snmpwalk_cache_multi_oid($device, 'OchOsTable', $pre_cache['infineragroove_portTable'], 'CORIANT-GROOVE-MIB');
}

foreach (array_keys($pre_cache['infineragroove_portTable']) as $index) {
    $indexids = explode('.', $index);

    if (isset($pre_cache['infineragroove_portTable'][$index]['ochOsAdminStatus'])) {
        $pre_cache['infineragroove_portTable'][$index]['portAlias'] = 'och-os-';
    } else {
        $pre_cache['infineragroove_portTable'][$index]['portAlias'] = 'port-';
    }
    $pre_cache['infineragroove_portTable'][$index]['portAlias'] .= $indexids[0] . '/' . $indexids[1] . '/' . $indexids[3];

    unset($indexids);
}

if (! is_array($pre_cache['infineragroove_slotTable'])) {
    $pre_cache['infineragroove_slotTable'] = [];
    echo ' slotTable';
    $pre_cache['infineragroove_slotTable'] = snmpwalk_cache_multi_oid($device, 'slotTable', $pre_cache['infineragroove_slotTable'], 'CORIANT-GROOVE-MIB');
    echo ' cardTable';
    $pre_cache['infineragroove_slotTable'] = snmpwalk_cache_multi_oid($device, 'cardTable', $pre_cache['infineragroove_slotTable'], 'CORIANT-GROOVE-MIB');
}

echo "\n";
