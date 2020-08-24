<?php
/**
 * LibreNMS - Ekinops 360 device support
 *
 * @category Network_Monitoring
 * @package  LibreNMS
 * @author   Najihel <github@ituz.fr>
 * @license  http://gnu.org/copyleft/gpl.html GNU GPL
 * @link     https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// **********  Ekinops 360 Devices
$data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.20044.7.1.5.0', '.1.3.6.1.4.1.20044.7.6.8']);

$hostname = $data['.1.3.6.1.4.1.20044.7.1.5.0'];
//$hostname need to be overwrite because SysName isn't customizable on Ekinops product. It's always "ekinops product" but the real hostname is stored on a specific OID.

list(,$hardware,$version) = explode(',', $device['sysDescr']);

$serial_snmp = explode('\n', $data['.1.3.6.1.4.1.20044.7.6.8']);
$serial_raw = explode(':', $serial_snmp[4]);
$serial = $serial_raw[1];
