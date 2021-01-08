<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'alaChasEntPhysFanTable';
$pre_cache['aos6_fan_oids'] = snmpwalk_cache_multi_oid($device, 'alaChasEntPhysFanTable', [], 'ALCATEL-IND1-CHASSIS-MIB', 'aos6', '-OQUse');

echo 'chasChassisEntry';
$pre_cache['aos6_temp_oids'] = snmpwalk_cache_multi_oid($device, 'chasChassisEntry', [], 'ALCATEL-IND1-CHASSIS-MIB', 'aos6', '-OQUse');

echo 'alaStackMgrChassisTable';
$pre_cache['aos6_stack_oids'] = snmpwalk_cache_multi_oid($device, 'alaStackMgrChassisTable', [], 'ALCATEL-IND1-STACK-MANAGER-MIB', 'aos6', '-OQUse');

echo 'chasControlCertifyStatus';
$pre_cache['aos6_sync_oids'] = snmpwalk_cache_multi_oid($device, 'chasControlCertifyStatus', [], 'ALCATEL-IND1-CHASSIS-MIB', 'aos6', '-OQUse');

echo 'alclnkaggAggEntry';
$pre_cache['aos6_lag_oids'] = snmpwalk_cache_multi_oid($device, 'alclnkaggAggEntry', [], 'ALCATEL-IND1-LAG-MIB', 'aos6', '-OQUse');
