<?php
/**
 * LibreNMS - ADVA device support - Pre-Cache for Sensors
 *
 * @category   Network_Monitoring
 * @package    LibreNMS
 * @subpackage ADVA device support
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// FSP150CC Series
if (starts_with($device['sysObjectID'], 'enterprises.2544.1.12.1.1')) {
	$pre_cache['fsp150'] = snmpwalk_cache_multi_oid($device, 'cmEntityObjects', array(), 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
}// FSP3000 R7 Series


// FSP3000 R7 Series
if (starts_with($device['sysObjectID'], 'enterprises.2544.1.11.1.1')) {

$pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'pmSnapshotCurrentEntry', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityFacilityOneIndex', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityDcnOneIndex', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxOneIndex', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityFacilityAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityDcnAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugMaxDataRate', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugAdmin', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'physicalPortFrequency', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugTransmitChannel', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugFiberType', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugReach', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');

    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $fsp3kr7_Card, 'ADVA-FSPR7-MIB', '', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'eqptPhysInstValueEntry', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'optMuxPhysInstValueTable', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'entityMtosiSlotsAidString', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'eqptPhysThresholdEntry', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '', '-OQUbs');

} // End of FSP3000 R7 Series
