<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.8741.6')) {
    $licenses = snmp_get($device, 'SNWL-SSLVPN-MIB::userLicense.0', '-Ovq');
    $licenses = str_replace(' Users', '', $licenses);

    $class = 'clients';
    $oid = '.1.3.6.1.4.1.8741.6.2.1.9.0'; // SNWL-SSLVPN-MIB::activeUserLicense.0
    $index = 0;
    $type = 'sonicwall';
    $descr = 'SSL VPN clients';
    $divisor = 1;
    $multiplier = 1;
    $low_limit = null;
    $low_warn_limit = 0;
    $warn_limit = $licenses - 10;
    $high_limit = $licenses;
    $current = snmp_get($device, $oid, '-Ovq');

    discover_sensor($valid['sensor'], $class, $device, $oid, $index, $type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current);
}
