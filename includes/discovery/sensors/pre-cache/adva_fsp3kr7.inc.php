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

// FSP3000 R7 Series
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'pmSnapshotCurrentEntry', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityFacilityOneIndex', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityDcnOneIndex', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxOneIndex', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityFacilityAidString', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityDcnAidString', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxAidString', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugMaxDataRate', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugAdmin', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'physicalPortFrequency', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugTransmitChannel', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugFiberType', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7'] = snmpwalk_cache_multi_oid($device, 'plugReach', $pre_cache['adva_fsp3kr7'], 'ADVA-FSPR7-MIB', null, '-OQUbs');

$pre_cache['adva_fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $pre_cache['adva_fsp3kr7_Card'], 'ADVA-FSPR7-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'eqptPhysInstValueEntry', $pre_cache['adva_fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'optMuxPhysInstValueTable', $pre_cache['adva_fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'entityMtosiSlotsAidString', $pre_cache['adva_fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', null, '-OQUbs');
$pre_cache['adva_fsp3kr7_Card'] = snmpwalk_cache_multi_oid($device, 'eqptPhysThresholdEntry', $pre_cache['adva_fsp3kr7_Card'], 'ADVA-FSPR7-PM-MIB', null, '-OQUbs');
