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
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

use Illuminate\Support\Str;

if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.8741.6')) {
    $licenses = snmp_get($device, 'SNWL-SSLVPN-MIB::userLicense.0', '-Ovq');
    $licenses = str_replace(' Users', '', $licenses);
    $current = snmp_get($device, '.1.3.6.1.4.1.8741.6.2.1.9.0', '-Ovq');

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        '.1.3.6.1.4.1.8741.6.2.1.9.0', // SNWL-SSLVPN-MIB::activeUserLicense.0
        0,
        'sonicwall',
        'SSL VPN clients',
        1,
        1,
        null,
        0,
        $licenses - 10,
        $licenses,
        $current
    );
}
