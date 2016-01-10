<?php

if ($device['os'] == 'asa' || $device['os'] == 'pix') {
    echo "ALTIGA-MIB SSL VPN Statistics \n";

    $tags = array();

    $oids = array(
        'alSslStatsTotalSessions',
        'alSslStatsActiveSessions',
        'alSslStatsMaxSessions',
        'alSslStatsPreDecryptOctets',
        'alSslStatsPostDecryptOctets',
        'alSslStatsPreEncryptOctets',
        'alSslStatsPostEncryptOctets',
    );

    $tags['rrd_def'] = array(
        'DS:TotalSessions:COUNTER:600:U:100000',
        'DS:ActiveSessions:GAUGE:600:0:U',
        'DS:MaxSessions:GAUGE:600:0:U',
        'DS:PreDecryptOctets:COUNTER:600:U:100000000000',
        'DS:PostDecryptOctets:COUNTER:600:U:100000000000',
        'DS:PreEncryptOctets:COUNTER:600:U:100000000000',
        'DS:PostEncryptOctets:COUNTER:600:U:100000000000',
    );

    $data_array = snmpwalk_cache_oid($device, $proto, array(), 'ALTIGA-SSL-STATS-MIB');

    $fields = array();

    $got_value = false;
    foreach ($oids as $oid) {
        if (is_numeric($data_array[0][$oid])) {
            $value = $data_array[0][$oid];
            if ($value > 0) {
                $got_value = true;
            }
        }
        else {
            $value = '0';
        }
        $fields[$oid] = $value;
    }

    if ($got_value) {
        data_update($device, 'altiga-ssl', $tags, $fields);
    }

    unset($tags, $fields, $oids, $data_array);
}//end if
