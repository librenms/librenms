<?php

/*
 * LibreNMS OS Polling module for Mitel Standard Linux
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$mslhw = $device['sysDescr'];
$version_oid = '.1.3.6.1.4.1.1027.4.1.2.1.1.1.4.10.1.3.6.1.4.1.1027.1.6.1';
$features_oid = '.1.3.6.1.4.1.1027.4.1.2.1.1.1.5.10.1.3.6.1.4.1.1027.1.6.1';
$oids = ['mitelAppTblProductVersion.10.1.3.6.1.4.1.1027.1.6.1', 'mitelAppTblProductDescr.10.1.3.6.1.4.1.1027.1.6.1'];
$mitelapptbl_data = snmp_get_multi_oid($device, $oids, '-OUQnt', 'MITEL-APPCMN-MIB');

$hardware = preg_replace('/;VerSw.*$/', '', (preg_replace('/^.*VerHw:/', '', $mslhw)));
$version = trim($mitelapptbl_data[$version_oid], '"');
$features = trim($mitelapptbl_data[$features_oid], '"');

unset(
    $mslhw,
    $mitelapptbl_data,
    $version_oid,
    $features_oid
);
