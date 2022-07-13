<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$enabled = dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'enabled'");
if ($enabled == 1) {
    if (dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'uuid'") == '') {
        dbInsert(['name' => 'uuid', 'value' => guidv4(openssl_random_pseudo_bytes(16))], 'callback');
    }

    $uuid = dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'uuid'");

    $version = version_info();
    $queries = [
        'alert_rules'     => 'SELECT COUNT(*) AS `total`,`severity` FROM `alert_rules` WHERE `disabled`=0 GROUP BY `severity`',
        'alert_templates' => 'SELECT COUNT(*) AS `total` FROM `alert_templates`',
        'api_tokens'      => 'SELECT COUNT(*) AS `total` FROM `api_tokens` WHERE `disabled`=0',
        'applications'    => 'SELECT COUNT(*) AS `total`,`app_type` FROM `applications` GROUP BY `app_type`',
        'bgppeer_state'   => 'SELECT COUNT(*) AS `total`,`bgpPeerState` FROM `bgpPeers` GROUP BY `bgpPeerState`',
        'bgppeer_status'  => 'SELECT COUNT(*) AS `total`,`bgpPeerAdminStatus` FROM `bgpPeers` GROUP BY `bgpPeerAdminStatus`',
        'bills'           => 'SELECT COUNT(*) AS `total`,`bill_type` FROM `bills` GROUP BY `bill_type`',
        'cef'             => 'SELECT COUNT(*) AS `total` FROM `cef_switching`',
        'cisco_asa'       => 'SELECT COUNT(*) AS `total`,`oid` FROM `ciscoASA` WHERE `disabled` = 0 GROUP BY `oid`',
        'mempool'         => 'SELECT COUNT(*) AS `total`,`mempool_descr` FROM `mempools` GROUP BY `mempool_descr`',
        'dbschema'        => 'SELECT COUNT(*) AS `total`, COUNT(*) AS `version` FROM `migrations`',
        'snmp_version'    => 'SELECT COUNT(*) AS `total`,`snmpver` FROM `devices` GROUP BY `snmpver`',
        'os'              => 'SELECT COUNT(*) AS `total`,`os` FROM `devices` GROUP BY `os`',
        'type'            => 'SELECT COUNT(*) AS `total`,`type` FROM `devices` GROUP BY `type`',
        'hardware'        => 'SELECT COUNT(*) AS `total`, `hardware` FROM `devices` GROUP BY `hardware`',
        'ipsec'           => 'SELECT COUNT(*) AS `total` FROM `ipsec_tunnels`',
        'ipv4_addresses'  => 'SELECT COUNT(*) AS `total` FROM `ipv4_addresses`',
        'ipv4_macaddress' => 'SELECT COUNT(*) AS `total` FROM ipv4_mac',
        'ipv4_networks'   => 'SELECT COUNT(*) AS `total` FROM ipv4_networks',
        'ipv6_addresses'  => 'SELECT COUNT(*) AS `total` FROM `ipv6_addresses`',
        'ipv6_networks'   => 'SELECT COUNT(*) AS `total` FROM `ipv6_networks`',
        'xdp'             => 'SELECT COUNT(*) AS `total`,`protocol` FROM `links` GROUP BY `protocol`',
        'ospf'            => 'SELECT COUNT(*) AS `total`,`ospfVersionNumber` FROM `ospf_instances` GROUP BY `ospfVersionNumber`',
        'ospf_links'      => 'SELECT COUNT(*) AS `total`,`ospfIfType` FROM `ospf_ports` GROUP BY `ospfIfType`',
        'arch'            => 'SELECT COUNT(*) AS `total`,`arch` FROM `packages` GROUP BY `arch`',
        'pollers'         => 'SELECT COUNT(*) AS `total` FROM `pollers`',
        'port_type'       => 'SELECT COUNT(*) AS `total`,`ifType` FROM `ports` GROUP BY `ifType`',
        'port_ifspeed'    => 'SELECT COUNT(*) AS `total`,ROUND(`ifSpeed`/1000/1000) FROM `ports` GROUP BY `ifSpeed`',
        'port_vlans'      => 'SELECT COUNT(*) AS `total`,`state` FROM `ports_vlans` GROUP BY `state`',
        'processes'       => 'SELECT COUNT(*) AS `total` FROM `processes`',
        'processors'      => 'SELECT COUNT(*) AS `total`,`processor_type` FROM `processors` GROUP BY `processor_type`',
        'pseudowires'     => 'SELECT COUNT(*) AS `total` FROM `pseudowires`',
        'sensors'         => 'SELECT COUNT(*) AS `total`,`sensor_class` FROM `sensors` GROUP BY `sensor_class`',
        'sla'             => 'SELECT COUNT(*) AS `total`,`rtt_type` FROM `slas` GROUP BY `rtt_type`',
        'wireless'        => 'SELECT COUNT(*) AS `total`,`sensor_class` FROM `wireless_sensors` GROUP BY `sensor_class`',
        'storage'         => 'SELECT COUNT(*) AS `total`,`storage_type` FROM `storage` GROUP BY `storage_type`',
        'toner'           => 'SELECT COUNT(*) AS `total`,`supply_type` FROM `printer_supplies` GROUP BY `supply_type`',
        'vlans'           => 'SELECT COUNT(*) AS `total`,`vlan_type` FROM `vlans` GROUP BY `vlan_type`',
        'vminfo'          => 'SELECT COUNT(*) AS `total`,`vm_type` FROM `vminfo` GROUP BY `vm_type`',
        'vmware'          => 'SELECT COUNT(*) AS `total` FROM `vminfo`',
        'vrfs'            => 'SELECT COUNT(*) AS `total` FROM `vrfs`',
        'mysql_version'   => 'SELECT 1 AS `total`, @@version AS `version`',
    ];

    foreach ($queries as $name => $query) {
        $data = dbFetchRows($query);
        $response[$name] = $data;
    }
    $response['php_version'][] = ['total' => 1, 'version' => $version['php_ver']];
    $response['python_version'][] = ['total' => 1, 'version' => $version['python_ver']];
    $response['rrdtool_version'][] = ['total' => 1, 'version' => $version['rrdtool_ver']];
    $response['netsnmp_version'][] = ['total' => 1, 'version' => $version['netsnmp_ver']];

    // collect sysDescr and sysObjectID for submission
    $device_info = dbFetchRows('SELECT COUNT(*) AS `count`,`os`, `sysDescr`, `sysObjectID` FROM `devices`
        WHERE `sysDescr` IS NOT NULL AND `sysObjectID` IS NOT NULL GROUP BY `os`, `sysDescr`, `sysObjectID`');

    // sanitize sysDescr
    $device_info = array_map(function ($entry) {
        // remove hostnames from linux, macosx, and SunOS
        $entry['sysDescr'] = preg_replace_callback('/^(Linux |Darwin |FreeBSD |SunOS )[A-Za-z0-9._\-]+ ([0-9.]{3,9})/', function ($matches) {
            return $matches[1] . 'hostname ' . $matches[2];
        }, $entry['sysDescr']);

        // wipe serial numbers, preserve the format
        $sn_patterns = ['/[A-Z]/', '/[a-z]/', '/[0-9]/'];
        $sn_replacements = ['A', 'a', '0'];
        $entry['sysDescr'] = preg_replace_callback(
            '/((s\/?n|serial num(ber)?)[:=]? ?)([a-z0-9.\-]{4,16})/i',
            function ($matches) use ($sn_patterns, $sn_replacements) {
                return $matches[1] . preg_replace($sn_patterns, $sn_replacements, $matches[4]);
            },
            $entry['sysDescr']
        );

        return $entry;
    }, $device_info);

    $output = [
        'uuid' => $uuid,
        'data' => $response,
        'info' => $device_info,
    ];
    $data = json_encode($output);
    $submit = ['data' => $data];

    $fields = '';
    foreach ($submit as $key => $value) {
        $fields .= $key . '=' . $value . '&';
    }

    rtrim($fields, '&');

    $post = curl_init();
    set_curl_proxy($post);
    curl_setopt($post, CURLOPT_URL, \LibreNMS\Config::get('callback_post'));
    curl_setopt($post, CURLOPT_POST, count($submit));
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($post);
} elseif ($enabled == 2) {
    $uuid = dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'uuid'");
    $fields = "uuid=$uuid";

    $clear = curl_init();
    set_curl_proxy($clear);
    curl_setopt($clear, CURLOPT_URL, \LibreNMS\Config::get('callback_clear'));
    curl_setopt($clear, CURLOPT_POST, count($clear));
    curl_setopt($clear, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($clear, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($clear);
    dbDelete('callback', '`name`="uuid"', []);
    dbUpdate(['value' => '0'], 'callback', '`name` = "enabled"', []);
}
