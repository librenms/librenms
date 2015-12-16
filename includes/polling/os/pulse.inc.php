<?php
/*
 * LibreNMS Pulse Secure OS information module
 *
 * Copyright (c) 2015 Christophe Martinet Chrisgfx <martinet.christophe@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

$version = trim(snmp_get($device, "productVersion.0", "-OQv", "PULSESECURE-PSG-MIB"),'"');
$hardware = "Juniper " . trim(snmp_get($device, "productName.0", "-OQv", "PULSESECURE-PSG-MIB"),'"');
$hostname = trim($poll_device['sysName'],'"');

$usersrrd  = $config['rrd_dir'].'/'.$device['hostname'].'/pulse_users.rrd';
$users = snmp_get($device, 'PULSESECURE-PSG-MIB::iveConcurrentUsers.0', '-OQv');

if (is_numeric($users)) {
    if (!is_file($usersrrd)) {
        rrdtool_create($usersrrd, ' DS:users:GAUGE:600:0:U'.$config['rrd_rra']);
    }
    rrdtool_update($usersrrd, "N:$users");
    $graphs['pulse_users'] = true;
}

$sessrrd  = $config['rrd_dir'].'/'.$device['hostname'].'/pulse_sessions.rrd';
$sessions = snmp_get($device, 'PULSESECURE-PSG-MIB::iveConcurrentUsers.0', '-OQv');

if (is_numeric($sessions)) {
    if (!is_file($sessrrd)) {
        rrdtool_create($sessrrd, ' DS:sessions:GAUGE:600:0:U '.$config['rrd_rra']);
    }
    rrdtool_update($sessrrd, "N:$sessions");
    $graphs['pulse_sessions'] = true;
}
