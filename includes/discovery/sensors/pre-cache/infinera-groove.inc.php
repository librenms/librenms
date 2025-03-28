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
 *
 * @copyright  2019 Nick Hilliard
 * @author     Nick Hilliard <nick@foobar.org>
 */
if (! isset($pre_cache['infineragroove_portTable']) || ! is_array($pre_cache['infineragroove_portTable'])) {
    echo 'Caching OIDs:';
    $pre_cache['infineragroove_portTable'] = [];
    echo ' portTable';
    $portTable = SnmpQuery::options('-OQ')->numericIndex()->hideMib()->walk('CORIANT-GROOVE-MIB::portTable')->valuesByIndex();
    $pre_cache['infineragroove_portTable'] = array_merge_recursive($pre_cache['infineragroove_portTable'], $portTable);
    echo ' OchOsTable';
    $OchOsTable = SnmpQuery::options('-OQ')->numericIndex()->hideMib()->walk('CORIANT-GROOVE-MIB::ochOsTable')->valuesByIndex();
    $pre_cache['infineragroove_portTable'] = array_merge_recursive($pre_cache['infineragroove_portTable'], $OchOsTable);
    echo ' bitErrorRatePostFecTable';
    $bitErrorRatePostFecTable = SnmpQuery::options('-OQ')->numericIndex()->hideMib()->walk('CORIANT-GROOVE-MIB::bitErrorRatePostFecTable')->valuesByIndex();
    $pre_cache['infineragroove_portTable'] = array_merge_recursive($pre_cache['infineragroove_portTable'], $bitErrorRatePostFecTable);
    echo ' bitErrorRatePreFecTable';
    $bitErrorRatePreFecTable = SnmpQuery::options('-OQ')->numericIndex()->hideMib()->walk('CORIANT-GROOVE-MIB::bitErrorRatePreFecTable')->valuesByIndex();
    $pre_cache['infineragroove_portTable'] = array_merge_recursive($pre_cache['infineragroove_portTable'], $bitErrorRatePreFecTable);
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

if (! isset($pre_cache['infineragroove_slotTable']) || ! is_array($pre_cache['infineragroove_slotTable'])) {
    $pre_cache['infineragroove_slotTable'] = [];
    echo ' slotTable';
    //$pre_cache['infineragroove_slotTable'] = snmpwalk_cache_multi_oid($device, 'slotTable', $pre_cache['infineragroove_slotTable'], 'CORIANT-GROOVE-MIB');
    $slotTable = SnmpQuery::options('-OQ')->numericIndex()->hideMib()->walk('CORIANT-GROOVE-MIB::slotTable')->valuesByIndex();
    $pre_cache['infineragroove_slotTable'] = array_merge_recursive($pre_cache['infineragroove_slotTable'], $slotTable);
    echo ' cardTable';
    //$pre_cache['infineragroove_slotTable'] = snmpwalk_cache_multi_oid($device, 'cardTable', $pre_cache['infineragroove_slotTable'], 'CORIANT-GROOVE-MIB');
    $cardTable = SnmpQuery::options('-OQ')->numericIndex()->hideMib()->walk('CORIANT-GROOVE-MIB::cardTable')->valuesByIndex();
    $pre_cache['infineragroove_slotTable'] = array_merge_recursive($pre_cache['infineragroove_slotTable'], $cardTable);
}
