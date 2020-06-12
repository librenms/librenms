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

echo 'ddmPortInfoEntry ';
$pre_cache['aos7_oids'] = snmpwalk_cache_multi_oid($device, 'ddmPortInfoEntry', [], 'ALCATEL-IND1-PORT-MIB', 'aos7');

echo 'alaChasEntPhysFanTable ';
$pre_cache['aos7_fan_oids'] = snmpwalk_cache_multi_oid($device, 'alaChasEntPhysFanTable', [], 'ALCATEL-IND1-CHASSIS-MIB', 'aos7', '-OQUse');

echo 'chasEntPhysOperStatus ';
$pre_cache['aos7_chas_oids'] = snmpwalk_cache_multi_oid($device, 'chasEntPhysOperStatus', [], 'ALCATEL-IND1-CHASSIS-MIB', 'aos7', '-OQUse');

echo 'entPhysicalName';
$pre_cache['aos7_phys_oids'] = snmpwalk_cache_multi_oid($device, 'entPhysicalName', [], 'ALCATEL-IND1-HEALTH-MIB', 'aos7', '-OQUse');
