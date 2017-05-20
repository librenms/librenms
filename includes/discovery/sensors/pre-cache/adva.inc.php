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
}// End of FSP150CC R7 Series


// FSP3000 R7 Series
if (starts_with($device['sysObjectID'], 'enterprises.2544.1.11.1.1')) {
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'pmSnapshotCurrentEntry', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityFacilityOneIndex', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityDcnOneIndex', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxOneIndex', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityFacilityAidString', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityDcnAidString', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxAidString', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugMaxDataRate', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugAdmin', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'physicalPortFrequency', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugTransmitChannel', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugFiberType', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugReach', $pre_cache['fsp3kr7'], 'ADVA-FSPR7-MIB', '', '-OQUbs');

    $pre_cache['fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $pre_cache['fsp3kr7_Card'], 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'eqptPhysInstValueEntry', $pre_cache['fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'optMuxPhysInstValueTable', $pre_cache['fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'entityMtosiSlotsAidString', $pre_cache['fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $pre_cache['fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'eqptPhysThresholdEntry', $pre_cache['fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
} // End of FSP3000 R7 Series
