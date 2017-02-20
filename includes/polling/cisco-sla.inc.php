<?php

use LibreNMS\RRD\RrdDefinition;

// Gather our SLA's from the DB.
$slas = dbFetchRows('SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0', array($device['device_id']));

if (count($slas) > 0) {
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
        $rrd_def = RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000);
        $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
        data_update($device, 'sla', $tags, $fields);

        // Let's gather some per-type fields.
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
                $rrd_def = RrdDefinition::make()
                    ->addDataset('PacketLossSD', 'GAUGE', 0)
                    ->addDataset('PacketLossDS', 'GAUGE', 0)
                    ->addDataset('PacketOutOfSequence', 'GAUGE', 0)
                    ->addDataset('PacketMIA', 'GAUGE', 0)
                    ->addDataset('PacketLateArrival', 'GAUGE', 0)
                    ->addDataset('MOS', 'GAUGE', 0)
                    ->addDataset('ICPIF', 'GAUGE', 0)
                    ->addDataset('OWAvgSD', 'GAUGE', 0)
                    ->addDataset('OWAvgDS', 'GAUGE', 0)
                    ->addDataset('AvgSDJ', 'GAUGE', 0)
                    ->addDataset('AvgDSJ', 'GAUGE', 0);
                $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                data_update($device, 'sla', $tags, $jitter);
                $fields = array_merge($fields, $jitter);
                break;
            case 'icmpjitter':
                $icmpjitter = array(
                    'PacketLoss' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.26'][$sla_nr],
                    'PacketOosSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.28'][$sla_nr],
                    'PacketOosDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.29'][$sla_nr],
                    'PacketLateArrival' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.32'][$sla_nr],
                    'JitterAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.45'][$sla_nr],
                    'JitterAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.46'][$sla_nr],
                    'LatencyOWAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.47'][$sla_nr],
                    'LatencyOWAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.48'][$sla_nr],
                    'JitterIAJOut' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.49'][$sla_nr],
                    'JitterIAJIn' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.50'][$sla_nr],
                );
                $rrd_name = array('sla', $sla_nr, $rtt_type);
                $rrd_def = RrdDefinition::make()
                    ->addDataset('PacketLoss', 'GAUGE', 0)
                    ->addDataset('PacketOosSD', 'GAUGE', 0)
                    ->addDataset('PacketOosDS', 'GAUGE', 0)
                    ->addDataset('PacketLateArrival', 'GAUGE', 0)
                    ->addDataset('JitterAvgSD', 'GAUGE', 0)
                    ->addDataset('JitterAvgDS', 'GAUGE', 0)
                    ->addDataset('LatencyOWAvgSD', 'GAUGE', 0)
                    ->addDataset('LatencyOWAvgDS', 'GAUGE', 0)
                    ->addDataset('JitterIAJOut', 'GAUGE', 0)
                    ->addDataset('JitterIAJIn', 'GAUGE', 0);
                $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                data_update($device, 'sla', $tags, $icmpjitter);
                $fields = array_merge($fields, $icmpjitter);
                break;
        }

        d_echo("The following datasources were collected for #".$sla['sla_nr'].":\n");
        d_echo($fields);

        // Update the DB if necessary
        if (count($update) > 0) {
            $updated = dbUpdate($update, 'slas', '`sla_id` = ?', array($sla['sla_id']));
        }
    }
}

unset($slas);
