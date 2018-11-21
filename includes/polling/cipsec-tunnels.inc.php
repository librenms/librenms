<?php

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'cisco') {
// FIXME - seems to be broken. IPs appear with leading zeroes.
    $ipsec_array = snmpwalk_cache_oid($device, 'cipSecTunnelEntry', array(), 'CISCO-IPSEC-FLOW-MONITOR-MIB');
    if (!empty($ipsec_array)) {
        $ike_array = snmpwalk_cache_oid($device, 'cikeTunnelEntry', array(), 'CISCO-IPSEC-FLOW-MONITOR-MIB');
    }

    $tunnels_db = dbFetchRows('SELECT * FROM `ipsec_tunnels` WHERE `device_id` = ?', array($device['device_id']));
    foreach ($tunnels_db as $tunnel) {
        if (empty($tunnel['peer_addr']) && empty($tunnel['local_addr'])) {
            dbDelete('ipsec_tunnels', '`tunnel_id` = ?', array($tunnel['tunnel_id']));
        }

        $tunnels[$tunnel['peer_addr']] = $tunnel;
    }

    $valid_tunnels = array();

    foreach ($ipsec_array as $index => $tunnel) {
        $tunnel = array_merge($tunnel, $ike_array[$tunnel['cipSecTunIkeTunnelIndex']]);

        echo "Tunnel $index (" . $tunnel['cipSecTunIkeTunnelIndex'] . ")\n";

        echo 'Address ' . $tunnel['cikeTunRemoteValue'] . "\n";

        $address = $tunnel['cikeTunRemoteValue'];

        $oids = array(
            'cipSecTunInOctets',
            'cipSecTunInDecompOctets',
            'cipSecTunInPkts',
            'cipSecTunInDropPkts',
            'cipSecTunInReplayDropPkts',
            'cipSecTunInAuths',
            'cipSecTunInAuthFails',
            'cipSecTunInDecrypts',
            'cipSecTunInDecryptFails',
            'cipSecTunOutOctets',
            'cipSecTunOutUncompOctets',
            'cipSecTunOutPkts',
            'cipSecTunOutDropPkts',
            'cipSecTunOutAuths',
            'cipSecTunOutAuthFails',
            'cipSecTunOutEncrypts',
            'cipSecTunOutEncryptFails',
        );

        $db_oids = array(
            'cipSecTunStatus' => 'tunnel_status',
            'cikeTunLocalName' => 'tunnel_name',
            'cikeTunLocalValue' => 'local_addr',
        );

        if (!is_array($tunnels[$tunnel['cikeTunRemoteValue']]) && !empty($tunnel['cikeTunRemoteValue'])) {
            $tunnel_id = dbInsert(array(
                'device_id' => $device['device_id'],
                'peer_addr' => $tunnel['cikeTunRemoteValue'],
                'local_addr' => $tunnel['cikeTunLocalValue'],
                'tunnel_name' => $tunnel['cikeTunLocalName']
            ), 'ipsec_tunnels');
            $valid_tunnels[] = $tunnel_id;
        } else {
            foreach ($db_oids as $db_oid => $db_value) {
                $db_update[$db_value] = $tunnel[$db_oid];
            }

            if (!empty($tunnels[$tunnel['cikeTunRemoteValue']]['tunnel_id'])) {
                $updated = dbUpdate(
                    $db_update,
                    'ipsec_tunnels',
                    '`tunnel_id` = ?',
                    array($tunnels[$tunnel['cikeTunRemoteValue']]['tunnel_id'])
                );
                $valid_tunnels[] = $tunnels[$tunnel['cikeTunRemoteValue']]['tunnel_id'];
            }
        }

        if (is_numeric($tunnel['cipSecTunHcInOctets']) &&
            is_numeric($tunnel['cipSecTunHcInDecompOctets']) &&
            is_numeric($tunnel['cipSecTunHcOutOctets']) &&
            is_numeric($tunnel['cipSecTunHcOutUncompOctets'])
        ) {
            echo 'HC ';

            $tunnel['cipSecTunInOctets'] = $tunnel['cipSecTunHcInOctets'];
            $tunnel['cipSecTunInDecompOctets'] = $tunnel['cipSecTunHcInDecompOctets'];
            $tunnel['cipSecTunOutOctets'] = $tunnel['cipSecTunHcOutOctets'];
            $tunnel['cipSecTunOutUncompOctets'] = $tunnel['cipSecTunHcOutUncompOctets'];
        }

        $rrd_name = array('ipsectunnel', $address);
        $rrd_def = new RrdDefinition();
        foreach ($oids as $oid) {
            $oid_ds = str_replace('cipSec', '', $oid);
            $rrd_def->addDataset($oid_ds, 'COUNTER', null, 1000000000);
        }

        $fields = array();

        foreach ($oids as $oid) {
            if (is_numeric($tunnel[$oid])) {
                $value = $tunnel[$oid];
            } else {
                $value = '0';
            }
            $fields[$oid] = $value;
        }

        if (isset($tunnel['cikeTunRemoteValue'])) {
            $tags = compact('address', 'rrd_name', 'rrd_def');
            data_update($device, 'ipsectunnel', $tags, $fields);

            // $graphs['ipsec_tunnels'] = TRUE;
        }
    }//end foreach

    if (!empty($valid_tunnels)) {
        d_echo($valid_tunnels);
        dbDelete(
            'ipsec_tunnels',
            "`tunnel_id` NOT IN " . dbGenPlaceholders(count($valid_tunnels)) . " AND `device_id`=?",
            array_merge([$device['device_id']], $valid_tunnels)
        );
    }

    unset($rrd_name, $rrd_def, $fields, $oids, $data, $data, $oid, $tunnel);
}

unset(
    $ipsec_array,
    $ike_array,
    $tunnels_db,
    $valid_tunnels
);
