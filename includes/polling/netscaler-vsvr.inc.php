<?php

// NS-ROOT-MIB::vsvrName."observium" = STRING: "observium"
// NS-ROOT-MIB::vsvrIpAddress."observium" = IpAddress: 195.78.84.141
// NS-ROOT-MIB::vsvrPort."observium" = INTEGER: 80
// NS-ROOT-MIB::vsvrType."observium" = INTEGER: http(0)
// NS-ROOT-MIB::vsvrState."observium" = INTEGER: up(7)
// NS-ROOT-MIB::vsvrCurClntConnections."observium" = Gauge32: 18
// NS-ROOT-MIB::vsvrCurSrvrConnections."observium" = Gauge32: 0
// NS-ROOT-MIB::vsvrSurgeCount."observium" = Counter32: 0
// NS-ROOT-MIB::vsvrTotalRequests."observium" = Counter64: 64532
// NS-ROOT-MIB::vsvrTotalRequestBytes."observium" = Counter64: 22223153
// NS-ROOT-MIB::vsvrTotalResponses."observium" = Counter64: 64496
// NS-ROOT-MIB::vsvrTotalResponseBytes."observium" = Counter64: 1048603453
// NS-ROOT-MIB::vsvrTotalPktsRecvd."observium" = Counter64: 629637
// NS-ROOT-MIB::vsvrTotalPktsSent."observium" = Counter64: 936237
// NS-ROOT-MIB::vsvrTotalSynsRecvd."observium" = Counter64: 43130
// NS-ROOT-MIB::vsvrCurServicesDown."observium" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesUnKnown."observium" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesOutOfSvc."observium" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesTransToOutOfSvc."observium" = Gauge32: 0
// NS-ROOT-MIB::vsvrCurServicesUp."observium" = Gauge32: 0
// NS-ROOT-MIB::vsvrTotMiss."observium" = Counter64: 0
// NS-ROOT-MIB::vsvrRequestRate."observium" = STRING: "0"
// NS-ROOT-MIB::vsvrRxBytesRate."observium" = STRING: "248"
// NS-ROOT-MIB::vsvrTxBytesRate."observium" = STRING: "188"
// NS-ROOT-MIB::vsvrSynfloodRate."observium" = STRING: "0"
// NS-ROOT-MIB::vsvrIp6Address."observium" = STRING: 0:0:0:0:0:0:0:0
// NS-ROOT-MIB::vsvrTotHits."observium" = Counter64: 64537
// NS-ROOT-MIB::vsvrTotSpillOvers."observium" = Counter32: 0
// NS-ROOT-MIB::vsvrTotalClients."observium" = Counter64: 43023
// NS-ROOT-MIB::vsvrClientConnOpenRate."observium" = STRING: "0"
if ($device['os'] == 'netscaler') {
    echo "Netscaler VServers\n";

    $oids_gauge = array(
                   'vsvrCurClntConnections',
                   'vsvrCurSrvrConnections',
                  );

    $oids_counter = array(
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
                    );

    $oids = array_merge($oids_gauge, $oids_counter);

    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);

    $rrd_create = $config['rrd_rra'];

    foreach ($oids_gauge as $oid) {
        $oid_ds          = truncate(str_replace('vsvr', '', $oid), 19, '');
        $rrd_create .= " DS:$oid_ds:GAUGE:600:U:100000000000";
    }

    foreach ($oids_counter as $oid) {
    $oid_ds          = truncate(str_replace('vsvr', '', $oid), 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
    }

    $vsvr_array = snmpwalk_cache_oid($device, 'vserverEntry', array(), 'NS-ROOT-MIB');

    $vsvr_db = dbFetchRows('SELECT * FROM `netscaler_vservers` WHERE `device_id` = ?', array($device['device_id']));
    foreach ($vsvr_db as $vsvr) {
        $vsvrs[$vsvr['vsvr_name']] = $vsvr;
        print_r($vsvr);
    }

    d_echo($vsvrs);

    foreach ($vsvr_array as $index => $vsvr) {
        if (isset($vsvr['vsvrName'])) {
            $vsvr_exist[$vsvr['vsvrName']] = 1;
            $rrd_file  = $config['rrd_dir'].'/'.$device['hostname'].'/netscaler-vsvr-'.safename($vsvr['vsvrName']).'.rrd';

            $fields = array();
            foreach ($oids as $oid) {
                if (is_numeric($vsvr[$oid])) {
                    $fields[$oid] = $vsvr[$oid];
                }
                else {
                    $fields[$oid] = 'U';
                }
            }

            echo str_pad($vsvr['vsvrName'], 25).' | '.str_pad($vsvr['vsvrType'], 5).' | '.str_pad($vsvr['vsvrState'], 6).' | '.str_pad($vsvr['vsvrIpAddress'], 16).' | '.str_pad($vsvr['vsvrPort'], 5);
            echo ' | '.str_pad($vsvr['vsvrRequestRate'], 8).' | '.str_pad($vsvr['vsvrRxBytesRate'].'B/s', 8).' | '.str_pad($vsvr['vsvrTxBytesRate'].'B/s', 8);

            $db_update = array(
                          'vsvr_ip'       => $vsvr['vsvrIpAddress'],
                          'vsvr_port'     => $vsvr['vsvrPort'],
                          'vsvr_state'    => $vsvr['vsvrState'],
                          'vsvr_type'     => $vsvr['vsvrType'],
                          'vsvr_req_rate' => $vsvr['RequestRate'],
                          'vsvr_bps_in'   => $vsvr['vsvrRxBytesRate'],
                          'vsvr_bps_out'  => $vsvr['vsvrTxBytesRate'],
                         );

            if (!is_array($vsvrs[$vsvr['vsvrName']])) {
                $db_insert = array_merge(array('device_id' => $device['device_id'], 'vsvr_name' => $vsvr['vsvrName']), $db_update);
                $vsvr_id   = dbInsert($db_insert, 'netscaler_vservers');
                echo ' +';
            }
            else {
                $updated = dbUpdate($db_update, 'netscaler_vservers', '`vsvr_id` = ?', array($vsvrs[$vsvr['vsvrName']]['vsvr_id']));
                echo ' U';
            }

            if (!file_exists($rrd_file)) {
                rrdtool_create($rrd_file, $rrd_create);
            }

            rrdtool_update($rrd_file, $fields);

            $tags = array('vsvrName' => $vsvr['vsvrName']);
            influx_update($device,'netscaler-vsvr',$tags,$fields);

            echo "\n";
        }//end if
    }//end foreach

    d_echo($vsvr_exist);

    foreach ($vsvrs as $db_name => $db_id) {
        if (!$vsvr_exist[$db_name]) {
            echo '-'.$db_name;
            dbDelete('netscaler_vservers', '`vsvr_id` =  ?', array($db_id));
        }
    }
}//end if
