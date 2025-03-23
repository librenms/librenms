<?php
/**
 * LibreNMS - Nokia PSD SFP DDM Sensors
 *
 * @category   Network_Monitoring
 *
 * @author     Nick Peelman <nick@peelman.us>
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

// *************************************************************
// ***** Pre-Cache for Nokia PSD
// *************************************************************

d_echo('Nokia PSD DDM Pre-Cache \n');

$pre_cache['iftable'] = snmpwalk_cache_multi_oid($device, 'IF-MIB::ifTable', null, 'IF-MIB', null, '-OQUbs');
$pre_cache['ifnames'] = snmpwalk_cache_multi_oid($device, 'IF-MIB::ifName', null, 'IF-MIB', null, '-OQUbs');
$pre_cache['ddmvalues'] = snmpwalk_cache_multi_oid($device, 'tnPsdDdmDataValue', null, 'TROPIC-PSD-MIB', 'nokia/1830', '-OQUbs');

//  ************** End of Pre-Cache for Nokia PSD **********
