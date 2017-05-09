<?php
/**
 * LibreNMS - ADVA FSP3000 R7 (DWDM) device support
 *
 * @category Network_Management
 * @package  LibreNMS
 * @author   Christoph Zilian <czilian@hotmail.com>
 * @license  http://gnu.org/copyleft/gpl.html GNU GPL
 * @link     https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

if ($device['os'] == 'advafsp3kr7') {
    echo 'Pre-cache ADVA FSP3000 R7 (advafsp3kr7):';
    echo "\n";

    $advafsp3kr7_oids = array();
    unset($fsp3kr7_Card);
    $fsp3kr7_Card     = array();

    echo 'Caching OIDs:'."\n";

    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'pmSnapshotCurrentEntry', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '', '-OQUbs');
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



    echo 'OIDs:'."\n";

    //$results = print_r($fsp3kr7_Card, true); // $results now contains output from print_r
    //file_put_contents('/opt/librenms/adva-precache.txt', $results);
    //var_dump($results);

    $test = $advafsp3kr7_oids;


    foreach (array_keys($test) as $index => $entity) {
        //$test[$index] = implode('.', explode('.' , $test[$entity] , -2));
        $aaa = array();

        foreach (array_keys($test) as $entity => $content) {
            $aaa[$index] = implode('.', explode('.', array_keys($test[$entity]), -2));

            if ($test[$content]['entityFacilityAidString']) {
                $test[$content]['AidString'] = $test[$content]['entityFacilityAidString'];
                $test[$content]['OneIndex']  = $test[$content]['entityFacilityOneIndex'];
            }
            if ($test[$content]['entityDcnAidString']) {
                $test[$content]['AidString'] = $test[$content]['entityDcnAidString'];
                $test[$content]['OneIndex']  = $test[$content]['entityDcnOneIndex'];
            }
            if ($test[$content]['entityOpticalMuxAidString']) {
                $test[$content]['AidString'] = $test[$content]['entityOpticalMuxAidString'];
                $test[$content]['OneIndex']  = $test[$content]['entityOpticalMuxOneIndex'];
            }
        }
        //   $content[$entity] = str_replace($replace, "", $content[$entity][$replace]);
        //        $entity = implode('.', explode('.', $entity, -2);
    } //end test
    //unset($advafsp3kr7_oids);
    $advafsp3kr7_oids = $test;
    //var_dump($aaa);
}   // end of OS condition
