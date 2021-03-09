<?php

d_echo('Entering Firebrick IPSec Tunnels');

if ($device['os_group'] == 'firebrick') {
    $ipsec_array = snmpwalk_cache_oid($device, 'fbIPsecConnectionEntry', [], 'FIREBRICK-IPSEC-MIB');

    $tunnels = [];

    $tunnels_db = dbFetchRows('SELECT * FROM `ipsec_tunnels` WHERE `device_id` = ?', [$device['device_id']]);
    foreach ($tunnels_db as $tunnel) {
        if (empty($tunnel['peer_addr']) && empty($tunnel['local_addr'])) {
            dbDelete('ipsec_tunnels', '`tunnel_id` = ?', [$tunnel['tunnel_id']]);
        }

        $tunnels[$tunnel['tunnel_name']] = $tunnel;
    }

    $tunnel_states = [
        0 => 'badconfig',
        1 => 'disabled',
        2 => 'waiting',
        3 => 'ondemand',
        4 => 'lingering',
        5 => 'reconnect-wait',
        6 => 'down',
        7 => 'initiating-eap',
        8 => 'initiating-auth',
        9 => 'initial',
        10 => 'closing',
        11 => 'childless',
        12 => 'active',
    ];
    $valid_tunnels = [];
    $db_oids = [
        'fbIPsecConnectionState' => 'tunnel_status',
        'fbIPsecConnectionName' => 'tunnel_name',
    ];

    foreach ($ipsec_array as $index => $tunnel) {
        echo "Tunnel $index (" . $tunnel['fbIPsecConnectionName'] . ")\n";

        echo 'Address ' . $tunnel['fbIPsecConnectionPeerAddress'] . "\n";

        if (! is_array($tunnels[$tunnel['fbIPsecConnectionName']]) && ! empty($tunnel['fbIPsecConnectionName'])) {
            $tunnel_id = dbInsert([
                'device_id' => $device['device_id'],
                'peer_addr' => $tunnel['fbIPsecConnectionPeerAddress'],
                'local_addr' => $device['hostname'],
                'tunnel_name' => $tunnel['fbIPsecConnectionName'],
                'tunnel_status' => $tunnel_states[$tunnel['fbIPsecConnectionState']],
            ], 'ipsec_tunnels');
            $valid_tunnels[] = $tunnel_id;
        } else {
            foreach ($db_oids as $db_oid => $db_value) {
                if ($db_value == 'tunnel_status') {
                    $db_update[$db_value] = $tunnel_states[$tunnel[$db_oid]];
                } else {
                    $db_update[$db_value] = $tunnel[$db_oid];
                }
            }

            if (! empty($tunnels[$tunnel['fbIPsecConnectionName']]['tunnel_id'])) {
                $updated = dbUpdate(
                    $db_update,
                    'ipsec_tunnels',
                    '`tunnel_id` = ?',
                    [$tunnels[$tunnel['fbIPsecConnectionName']]['tunnel_id']]
                );
                $valid_tunnels[] = $tunnels[$tunnel['fbIPsecConnectionName']]['tunnel_id'];
            }
        }
    }

    if (! empty($valid_tunnels)) {
        d_echo($valid_tunnels);
        dbDelete(
            'ipsec_tunnels',
            '`device_id`=? AND `tunnel_id` NOT IN ' . dbGenPlaceholders(count($valid_tunnels)),
            array_merge([$device['device_id']], $valid_tunnels)
        );
    }
}
