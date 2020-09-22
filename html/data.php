<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */
$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (is_numeric($_GET['id']) && (\LibreNMS\Config::get('allow_unauth_graphs') || port_permitted($_GET['id']))) {
    $port = cleanPort(get_port_by_id($_GET['id']));
    $device = device_by_id_cache($port['device_id']);
    $title = generate_device_link($device);
    $title .= ' :: Port  ' . generate_port_link($port);
    $auth = true;

    $in = snmp_get($device, 'ifHCInOctets.' . $port['ifIndex'], '-OUqnv', 'IF-MIB');
    if (empty($in)) {
        $in = snmp_get($device, 'ifInOctets.' . $port['ifIndex'], '-OUqnv', 'IF-MIB');
    }

    $out = snmp_get($device, 'ifHCOutOctets.' . $port['ifIndex'], '-OUqnv', 'IF-MIB');
    if (empty($out)) {
        $out = snmp_get($device, 'ifOutOctets.' . $port['ifIndex'], '-OUqnv', 'IF-MIB');
    }

    $time = microtime(true);

    printf("%lf|%s|%s\n", $time, $in, $out);
}
