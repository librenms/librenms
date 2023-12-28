<?php
/*
 * LibreNMS
 *
 * weos.inc.php
 *
 * LibreNMS os sensor pre-cache module for Westermo WeOS
 *
 * This program is free software: you can redistribute it and/or modify
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'ifName ';
$pre_cache['weos_ifName'] = snmpwalk_cache_oid($device, 'ifName', [], 'IF-MIB');
var_dump($pre_cache['weos_ifName']);

echo 'hdsl2ShdslEndpointCurrTable ';
$pre_cache['weos_hdsl2ShdslEndpointCurrTable'] = snmpwalk_cache_oid($device, 'hdsl2ShdslEndpointCurrTable', [], 'HDSL2-SHDSL-LINE-MIB', null, '-OQUbs');
var_dump($pre_cache['weos_hdsl2ShdslEndpointCurrTable']);
