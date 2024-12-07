<?php
/**
 * LibreNMS - ADVA AOS device support - Pre-Cache for Sensors
 *
 * @category   Network_Monitoring
 *
 * @author     Fabien VINCENT <fabien.vincent@i3d.net>
 * @license    https://gnu.org/copyleft/gpl.html GNU GPL
 *
 * @link       https://github.com/librenms/librenms/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// Get ifDescr from ADVA AOS
$pre_cache = snmpwalk_cache_multi_oid($device, 'ifDescr', [], 'IF-MIB', null, '-OQUbs');

foreach ($pre_cache as $index => $port) {
    /*
     * Replace the prefix node X interface from the interface description
     * It helps to have clearer graphs as $SHELF/$CARD/$IFNAME is more usable
     * than node $SHELF interface  $SHELF/$CARD/$IFNAME
     */
    $newDescr = preg_replace('/^node [0-9]+ interface (.+)/', '$1', $port['ifDescr']);
    $pre_cache[$index]['ifDescr'] = $newDescr;
}
