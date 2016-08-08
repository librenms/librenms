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
        // Lets process each SLA
        $unixtime = intval(($rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.5'][$sla['sla_nr']] / 100 + $time_offset));
        $time  = strftime('%Y-%m-%d %H:%M:%S', $unixtime);
        $update = array();

        // Use Nagios Status codes.
        $opstatus = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.2'][$sla['sla_nr']];
        if ($opstatus == 1) {
            $opstatus = 0;        // 0=Good
        } else {
            $opstatus = 2;        // 2=Critical
        }

        // Populating the update array means we need to update the DB.
        if ($opstatus != $sla['opstatus']) {
            $update['opstatus'] = $opstatus;
        }

        $rtt = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.1'][$sla['sla_nr']];
        echo 'SLA '.$sla['sla_nr'].': '.$sla['rtt_type'].' '.$sla['owner'].' '.$sla['tag'].'... '.$rtt.'ms at '.$time.'\n';

        $metrics = array(
            'rtt' => $rtt,
        );

        // The base RRD
        $filename = 'sla-'.$sla['sla_nr'].'.rrd';
        $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ($filename);
        if (!file_exists ($rrd_filename)) {
            rrdtool_create ($rrd_filename, " DS:rtt:GAUGE:600:0:300000" . $config['rrd_rra']);
        }
        rrdtool_update($rrd_filename, $metrics);

        // Let's gather some per-type metrics.
        switch ($sla['rtt_type']) {
            case 'jitter':
                $jitter = array(
                    'PacketLossSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.26'][$sla['sla_nr']],
                    'PacketLossDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.27'][$sla['sla_nr']],
                    'PacketOutOfSequence' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.28'][$sla['sla_nr']],
                    'PacketMIA' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.29'][$sla['sla_nr']],
                    'PacketLateArrival' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.30'][$sla['sla_nr']],
                    'MOS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.42'][$sla['sla_nr']]/100,
                    'ICPIF' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.43'][$sla['sla_nr']],
                    'OWAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.49'][$sla['sla_nr']],
                    'OWAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.50'][$sla['sla_nr']],
                    'AvgSDJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.47'][$sla['sla_nr']],
                    'AvgDSJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.48'][$sla['sla_nr']],
                );
                $filename = 'sla-'.$sla['sla_nr'].'-jitter.rrd';
                $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ($filename);
                if (!file_exists ($rrd_filename)) {
                    rrdtool_create ($rrd_filename, " DS:PacketLossSD:GAUGE:600:0:U DS:PacketLossDS:GAUGE:600:0:U DS:PacketOutOfSequence:GAUGE:600:0:U DS:PacketMIA:GAUGE:600:0:U DS:PacketLateArrival:GAUGE:600:0:U DS:MOS:GAUGE:600:0:U DS:ICPIF:GAUGE:600:0:U DS:OWAvgSD:GAUGE:600:0:U DS:OWAvgDS:GAUGE:600:0:U DS:AvgSDJ:GAUGE:600:0:U DS:AvgDSJ:GAUGE:600:0:U" . $config['rrd_rra']);
                }
                rrdtool_update($rrd_filename, $jitter);
                $metrics = array_merge($metrics,$jitter);
                break;
        }

        d_echo("The following metrics were collected for #".$sla['sla_nr'].":\n");
        d_echo($metrics);

        // Update influx
        $tags = array('sla_nr' => $sla['sla_nr']);
        influx_update($device,'sla',$tags,$metrics);

        // Update the DB if necessary
        if (count($update) > 0) {
            $updated = dbUpdate($update, 'slas', '`sla_id` = ?', array($sla['sla_id']));
        }
    }
}
