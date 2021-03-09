<?php
/**
 * LibreNMS - ADVA device support - Pre-Cache for Sensors
 *
 * @category   Network_Monitoring
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    https://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// FSP150CC Series
$pre_cache['adva_fsp150'] = snmpwalk_cache_multi_oid($device, 'cmEntityObjects', [], 'CM-ENTITY-MIB', null, '-OQUbs');

$neType = $pre_cache['adva_fsp150'][1]['neType'];
if ($neType == 'ccxg116pro') {
    $pre_cache['adva_fsp150_ports'] = snmpwalk_cache_multi_oid($device, 'cmEthernetTrafficPortTable', $pre_cache['adva_fsp150_ports'], 'CM-FACILITY-MIB', null, '-OQUbs');
} else {
    $pre_cache['adva_fsp150_ports'] = snmpwalk_cache_multi_oid($device, 'cmEthernetNetPortTable', $pre_cache['adva_fsp150_ports'], 'CM-FACILITY-MIB', null, '-OQUbs');
    $pre_cache['adva_fsp150_ports'] = snmpwalk_cache_multi_oid($device, 'cmEthernetAccPortTable', $pre_cache['adva_fsp150_ports'], 'CM-FACILITY-MIB', null, '-OQUbs');
}
