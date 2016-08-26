<?php

// Gather our SLA's from the DB.
$slas = dbFetchRows('SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0', array($device['device_id']));

if (count($slas > 0)) {
    // We have SLA's, lets go!!!

    // Go get some data from the device.
    $rttMonLatestRttOperTable = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.42.1.2.10.1', 1);
    $rttMonLatestOper = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.42.1.5', 1);

    $uptime      = snmp_get($device, 'sysUpTime.0', '-Otv');
    $time_offset = (time() - intval($uptime) / 100);

    foreach ($slas as $sla) {
        $sla_nr = $sla['sla_nr'];
        $rtt_type = $sla['rtt_type'];

        // Lets process each SLA
        $unixtime = intval(($rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.5'][$sla_nr] / 100 + $time_offset));
        $time  = strftime('%Y-%m-%d %H:%M:%S', $unixtime);
        $update = array();

        // Use Nagios Status codes.
        $opstatus = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.2'][$sla_nr];
        if ($opstatus == 1) {
            $opstatus = 0;        // 0=Good
        } else {
            $opstatus = 2;        // 2=Critical
        }

        // Populating the update array means we need to update the DB.
        if ($opstatus != $sla['opstatus']) {
            $update['opstatus'] = $opstatus;
        }

        $rtt = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.1'][$sla_nr];
        echo 'SLA '.$sla_nr.': '.$rtt_type.' '.$sla['owner'].' '.$sla['tag'].'... '.$rtt.'ms at '.$time.'\n';

        $fields = array(
            'rtt' => $rtt,
        );

        // The base RRD
        $rrd_name = array('sla', $sla_nr);
        $rrd_def = 'DS:rtt:GAUGE:600:0:300000';
        $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
        data_update($device, 'sla', $tags, $fields);

        // Let's gather some per-type metrics.
        switch ($rtt_type) {
            case 'jitter':
                $jitter = array(
                    'PacketLossSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.26'][$sla_nr],
                    'PacketLossDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.27'][$sla_nr],
                    'PacketOutOfSequence' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.28'][$sla_nr],
                    'PacketMIA' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.29'][$sla_nr],
                    'PacketLateArrival' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.30'][$sla_nr],
                    'MOS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.42'][$sla_nr]/100,
                    'ICPIF' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.43'][$sla_nr],
                    'OWAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.49'][$sla_nr],
                    'OWAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.50'][$sla_nr],
                    'AvgSDJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.47'][$sla_nr],
                    'AvgDSJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.48'][$sla_nr],
                );
                $rrd_name = array('sla', $sla_nr, $rtt_type);
                $rrd_def = array(
                    'DS:PacketLossSD:GAUGE:600:0:U',
                    'DS:PacketLossDS:GAUGE:600:0:U',
                    'DS:PacketOutOfSequence:GAUGE:600:0:U',
                    'DS:PacketMIA:GAUGE:600:0:U',
                    'DS:PacketLateArrival:GAUGE:600:0:U',
                    'DS:MOS:GAUGE:600:0:U',
                    'DS:ICPIF:GAUGE:600:0:U',
                    'DS:OWAvgSD:GAUGE:600:0:U',
                    'DS:OWAvgDS:GAUGE:600:0:U',
                    'DS:AvgSDJ:GAUGE:600:0:U',
                    'DS:AvgDSJ:GAUGE:600:0:U',
                );
                $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                data_update($device, 'sla', $tags, $jitter);
                $metrics = array_merge($metrics, $jitter);
                break;
        }

        d_echo("The following metrics were collected for #".$sla['sla_nr'].":\n");
        d_echo($metrics);

        // Update the DB if necessary
        if (count($update) > 0) {
            $updated = dbUpdate($update, 'slas', '`sla_id` = ?', array($sla['sla_id']));
        }
    }
}
