<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage Juniper BTI Switch Device Support - os module
 * @link       http://librenms.org
 * @copyright  2018 Christoph Zilian <czilian@hotmail.com>
 * @author     Christoph Zilian <czilian@hotmail.com>
*/

$version  = trim(snmp_get($device, "neSWVersion.0", "-OQv", "BTI-7000-MIB"), '"');
$hardware = trim(snmp_get($device, "shelfInvShortName.1", "-OQv", "BTI-7000-MIB"), '"')
            .' V'.trim(snmp_get($device, "shelfInvRev.1", "-OQv", "BTI-7000-MIB"), '"');
$serial  = trim(snmp_get($device, "shelfInvChassisPEC.1", "-OQv", "BTI-7000-MIB"), '"');
$features = implode(', ', explode(PHP_EOL, snmp_walk($device, 'slotInvPackName', '-Oqvs', 'BTI-7000-MIB')));

//$does_not_exist = snmpwalk_cache_oid($device, 'pvxEthSrvcTransportType', $port_stats, 'PACKET-VX-BRIDGE-MIB');
