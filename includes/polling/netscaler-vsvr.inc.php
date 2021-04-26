<?php

// NS-ROOT-MIB::vsvrFullName."librenms" = STRING: "librenms"
// NS-ROOT-MIB::vsvrIpAddress."librenms" = IpAddress: 195.78.84.141
// NS-ROOT-MIB::vsvrPort."librenms" = INTEGER: 80
// NS-ROOT-MIB::vsvrType."librenms" = INTEGER: http(0)
// NS-ROOT-MIB::vsvrState."librenms" = INTEGER: up(7)
// NS-ROOT-MIB::vsvrCurClntConnections."librenms" = Gauge32: 18
// NS-ROOT-MIB::vsvrCurSrvrConnections."librenms" = Gauge32: 0
// NS-ROOT-MIB::vsvrSurgeCount."librenms" = Counter32: 0
// NS-ROOT-MIB::vsvrTotalRequests."librenms" = Counter64: 64532
// NS-ROOT-MIB::vsvrTotalRequestBytes."librenms" = Counter64: 22223153
// NS-ROOT-MIB::vsvrTotalResponses."librenms" = Counter64: 64496
// NS-ROOT-MIB::vsvrTotalResponseBytes."librenms" = Counter64: 1048603453
// NS-ROOT-MIB::vsvrTotalPktsRecvd."librenms" = Counter64: 629637
// NS-ROOT-MIB::vsvrTotalPktsSent."librenms" = Counter64: 936237
// NS-ROOT-MIB::vsvrTotalSynsRecvd."librenms" = Counter64: 43130
// NS-ROOT-MIB::vsvrCurServicesDown."librenms" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesUnKnown."librenms" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesOutOfSvc."librenms" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesTransToOutOfSvc."librenms" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesUp."librenms" = Gauge32: 0
// NS-ROOT-MIB::vsvrTotMiss."librenms" = Counter64: 0
// NS-ROOT-MIB::vsvrRequestRate."librenms" = STRING: "0"
// NS-ROOT-MIB::vsvrRxBytesRate."librenms" = STRING: "248"
// NS-ROOT-MIB::vsvrTxBytesRate."librenms" = STRING: "188"
// NS-ROOT-MIB::vsvrSynfloodRate."librenms" = STRING: "0"
// NS-ROOT-MIB::vsvrIp6Address."librenms" = STRING: 0:0:0:0:0:0:0:0
// NS-ROOT-MIB::vsvrTotHits."librenms" = Counter64: 64537
// NS-ROOT-MIB::vsvrTotSpillOvers."librenms" = Counter32: 0
// NS-ROOT-MIB::vsvrTotalClients."librenms" = Counter64: 43023
// NS-ROOT-MIB::vsvrClientConnOpenRate."librenms" = STRING: "0"
use LibreNMS\RRD\RrdDefinition;

if ($device['os'] == 'netscaler') {
    $oids_gauge = [
        'vsvrCurClntConnections',
        'vsvrCurSrvrConnections',
    ];

    $oids_counter = [
        'vsvrSurgeCount',
        'vsvrTotalRequests',
        'vsvrTotalRequestBytes',
        'vsvrTotalResponses',
        'vsvrTotalResponseBytes',
        'vsvrTotalPktsRecvd',
        'vsvrTotalPktsSent',
        'vsvrTotalSynsRecvd',
        'vsvrTotMiss',
        'vsvrTotHits',
        'vsvrTotSpillOvers',
        'vsvrTotalClients',
    ];

    $oids = array_merge($oids_gauge, $oids_counter);

    $rrd_def = new RrdDefinition();
    foreach ($oids_gauge as $oid) {
        $oid_ds = str_replace('vsvr', '', $oid);
        $rrd_def->addDataset($oid_ds, 'GAUGE', null, 100000000000);
    }
    foreach ($oids_counter as $oid) {
        $oid_ds = str_replace('vsvr', '', $oid);
        $rrd_def->addDataset($oid_ds, 'COUNTER', null, 100000000000);
    }

    $vsvr_array = snmpwalk_cache_oid($device, 'vserverEntry', [], 'NS-ROOT-MIB');

    $vsvr_db = dbFetchRows('SELECT * FROM `netscaler_vservers` WHERE `device_id` = ?', [$device['device_id']]);
    foreach ($vsvr_db as $vsvr) {
        $vsvrs[$vsvr['vsvr_name']] = $vsvr;
        print_r($vsvr);
    }

    d_echo($vsvrs);

    foreach ($vsvr_array as $index => $vsvr) {
        if (isset($vsvr['vsvrFullName'])) {
            $vsvr_exist[$vsvr['vsvrFullName']] = 1;
            $rrd_name = 'netscaler-vsvr-' . $vsvr['vsvrFullName'];

            $fields = [];
            foreach ($oids as $oid) {
                $oid_ds = str_replace('vsvr', '', $oid);
                if (is_numeric($vsvr[$oid])) {
                    $fields[$oid_ds] = $vsvr[$oid];
                } else {
                    $fields[$oid_ds] = 'U';
                }
            }

            $tags = [
                'vsvrFullName' => $vsvr['vsvrFullName'],
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def,
            ];
            data_update($device, 'netscaler-vsvr', $tags, $fields);

            echo str_pad($vsvr['vsvrFullName'], 25) . ' | ' . str_pad($vsvr['vsvrType'], 5) . ' | ' . str_pad($vsvr['vsvrState'], 6) . ' | ' . str_pad($vsvr['vsvrIpAddress'], 16) . ' | ' . str_pad($vsvr['vsvrPort'], 5);
            echo ' | ' . str_pad($vsvr['vsvrRequestRate'], 8) . ' | ' . str_pad($vsvr['vsvrRxBytesRate'] . 'B/s', 8) . ' | ' . str_pad($vsvr['vsvrTxBytesRate'] . 'B/s', 8);

            $db_update = [
                'vsvr_ip'       => $vsvr['vsvrIpAddress'],
                'vsvr_port'     => $vsvr['vsvrPort'],
                'vsvr_state'    => $vsvr['vsvrState'],
                'vsvr_type'     => $vsvr['vsvrType'],
                'vsvr_req_rate' => $vsvr['RequestRate'],
                'vsvr_bps_in'   => $vsvr['vsvrRxBytesRate'],
                'vsvr_bps_out'  => $vsvr['vsvrTxBytesRate'],
            ];

            if (! is_array($vsvrs[$vsvr['vsvrFullName']])) {
                $db_insert = array_merge(['device_id' => $device['device_id'], 'vsvr_name' => $vsvr['vsvrFullName']], $db_update);
                $vsvr_id = dbInsert($db_insert, 'netscaler_vservers');
                echo ' +';
            } else {
                $updated = dbUpdate($db_update, 'netscaler_vservers', '`vsvr_id` = ?', [$vsvrs[$vsvr['vsvrFullName']]['vsvr_id']]);
                echo ' U';
            }

            echo "\n";
        }//end if
    }//end foreach

    d_echo($vsvr_exist);

    foreach ($vsvrs as $db_name => $db_id) {
        if (! $vsvr_exist[$db_name]) {
            echo '-' . $db_name;
            dbDelete('netscaler_vservers', '`vsvr_id` =  ?', [$db_id]);
        }
    }
}//end if
