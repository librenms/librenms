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
        dbInsert(array('name' => 'uuid', 'value' => guidv4(openssl_random_pseudo_bytes(16))), 'callback');
    }

    $uuid = dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'uuid'");

    $queries = array(
        'alert_rules'     => 'SELECT COUNT(`severity`) AS `total`,`severity` FROM `alert_rules` WHERE `disabled`=0 GROUP BY `severity`',
        'alert_templates' => 'SELECT COUNT(`id`) AS `total` FROM `alert_templates`',
        'api_tokens'      => 'SELECT COUNT(`id`) AS `total` FROM `api_tokens` WHERE `disabled`=0',
        'applications'    => 'SELECT COUNT(`app_type`) AS `total`,`app_type` FROM `applications` GROUP BY `app_type`',
        'bgppeer_state'   => 'SELECT COUNT(`bgpPeerState`) AS `total`,`bgpPeerState` FROM `bgpPeers` GROUP BY `bgpPeerState`',
        'bgppeer_status'  => 'SELECT COUNT(`bgpPeerAdminStatus`) AS `total`,`bgpPeerAdminStatus` FROM `bgpPeers` GROUP BY `bgpPeerAdminStatus`',
        'bills'           => 'SELECT COUNT(`bill_type`) AS `total`,`bill_type` FROM `bills` GROUP BY `bill_type`',
        'cef'             => 'SELECT COUNT(`device_id`) AS `total` FROM `cef_switching`',
        'cisco_asa'       => 'SELECT COUNT(`oid`) AS `total`,`oid` FROM `ciscoASA` WHERE `disabled` = 0 GROUP BY `oid`',
        'mempool'         => 'SELECT COUNT(`cmpName`) AS `total`,`cmpName` FROM `cmpMemPool` GROUP BY `cmpName`',
        'current'         => 'SELECT COUNT(`current_type`) AS `total`,`current_type` FROM `current` GROUP BY `current_type`',
        'dbschema'        => 'SELECT COUNT(`version`) AS `total`, `version` FROM `dbSchema`',
        'snmp_version'    => 'SELECT COUNT(`snmpver`) AS `total`,`snmpver` FROM `devices` GROUP BY `snmpver`',
        'os'              => 'SELECT COUNT(`os`) AS `total`,`os` FROM `devices` GROUP BY `os`',
        'type'            => 'SELECT COUNT(`type`) AS `total`,`type` FROM `devices` GROUP BY `type`',
        'hardware'        => 'SELECT COUNT(`device_id`) AS `total`, `hardware` FROM `devices` GROUP BY `hardware`',
        'ipsec'           => 'SELECT COUNT(`device_id`) AS `total` FROM `ipsec_tunnels`',
        'ipv4_addresses'  => 'SELECT COUNT(`ipv4_address_id`) AS `total` FROM `ipv4_addresses`',
        'ipv4_macaddress' => 'SELECT COUNT(`port_id`) AS `total` FROM ipv4_mac',
        'ipv4_networks'   => 'SELECT COUNT(`ipv4_network_id`) AS `total` FROM ipv4_networks',
        'ipv6_addresses'  => 'SELECT COUNT(`ipv6_address_id`) AS `total` FROM `ipv6_addresses`',
        'ipv6_networks'   => 'SELECT COUNT(`ipv6_network_id`) AS `total` FROM `ipv6_networks`',
        'xdp'             => 'SELECT COUNT(`id`) AS `total`,`protocol` FROM `links` GROUP BY `protocol`',
        'ospf'            => 'SELECT COUNT(`device_id`) AS `total`,`ospfVersionNumber` FROM `ospf_instances` GROUP BY `ospfVersionNumber`',
        'ospf_links'      => 'SELECT COUNT(`device_id`) AS `total`,`ospfIfType` FROM `ospf_ports` GROUP BY `ospfIfType`',
        'arch'            => 'SELECT COUNT(`pkg_id`) AS `total`,`arch` FROM `packages` GROUP BY `arch`',
        'pollers'         => 'SELECT COUNT(`id`) AS `total` FROM `pollers`',
        'port_type'       => 'SELECT COUNT(`port_id`) AS `total`,`ifType` FROM `ports` GROUP BY `ifType`',
        'port_ifspeed'    => 'SELECT COUNT(`ifSpeed`) AS `total`,ROUND(`ifSpeed`/1000/1000) FROM `ports` GROUP BY `ifSpeed`',
        'port_vlans'      => 'SELECT COUNT(`device_id`) AS `total`,`state` FROM `ports_vlans` GROUP BY `state`',
        'processes'       => 'SELECT COUNT(`device_id`) AS `total` FROM `processes`',
        'processors'      => 'SELECT COUNT(`processor_id`) AS `total`,`processor_type` FROM `processors` GROUP BY `processor_type`',
        'pseudowires'     => 'SELECT COUNT(`pseudowire_id`) AS `total` FROM `pseudowires`',
        'sensors'         => 'SELECT COUNT(`sensor_id`) AS `total`,`sensor_class` FROM `sensors` GROUP BY `sensor_class`',
        'storage'         => 'SELECT COUNT(`storage_id`) AS `total`,`storage_type` FROM `storage` GROUP BY `storage_type`',
        'toner'           => 'SELECT COUNT(`toner_id`) AS `total`,`toner_type` FROM `toner` GROUP BY `toner_type`',
        'vlans'           => 'SELECT COUNT(`vlan_id`) AS `total`,`vlan_type` FROM `vlans` GROUP BY `vlan_type`',
        'vminfo'          => 'SELECT COUNT(`id`) AS `total`,`vm_type` FROM `vminfo` GROUP BY `vm_type`',
        'vmware'          => 'SELECT COUNT(`id`) AS `total` FROM `vmware_vminfo`',
        'vrfs'            => 'SELECT COUNT(`vrf_id`) AS `total` FROM `vrfs`',
        'mysql_version'   => 'SELECT 1 AS `total`, @@version AS `version`',
    );


    foreach ($queries as $name => $query) {
        $data            = dbFetchRows($query);
        $response[$name] = $data;
    }

    $output = array(
        'uuid' => $uuid,
        'data' => $response,
    );
    $data   = json_encode($output);
    $submit = array('data' => $data);

    $fields = '';
    foreach ($submit as $key => $value) {
        $fields .= $key.'='.$value.'&';
    }

    rtrim($fields, '&');

    $post = curl_init();
    set_curl_proxy($post);
    curl_setopt($post, CURLOPT_URL, $config['callback_post']);
    curl_setopt($post, CURLOPT_POST, count($submit));
    curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($post);
}
else if ($enabled == 2) {
    $uuid   = dbFetchCell("SELECT `value` FROM `callback` WHERE `name` = 'uuid'");
    $fields = "uuid=$uuid";

    $clear = curl_init();
    set_curl_proxy($clear);
    curl_setopt($clear, CURLOPT_URL, $config['callback_clear']);
    curl_setopt($clear, CURLOPT_POST, count($clear));
    curl_setopt($clear, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($clear, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($clear);
    dbDelete('callback', '`name`="uuid"', array());
    dbUpdate(array('value' => '0'), 'callback', '`name` = "enabled"', array());
}
