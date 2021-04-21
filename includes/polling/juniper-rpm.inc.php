<?php

use LibreNMS\RRD\RrdDefinition;

// Function to fix the 0 missing before digit on a date from the MIB
function fixdate ($string)
{
    $datetime = explode(",", $string);
    $date = explode("-", $datetime[0]);
    $time = explode(":", $datetime[1]);
    
    // If one digit, add a 0 before
    foreach ($date as &$field) {
        if ((int)$field < 10) {
            $field = "0".$field;
        }
    }
    foreach ($time as &$field) {
        if ((int)$field < 10) {
            $field = "0".$field;
        }
    }
    // To remove the decisecond
    $time[2] = explode(".", $time[2])[0];

    return $date[0] . "-" . $date[1] . "-" . $date[2] . " " . $time[0] . ":" . $time[1] . ":" . $time[2];
}

// Gather our SLA's from the DB.
$slas = dbFetchRows('SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0', [$device['device_id']]);

if (count($slas) > 0) {
    // We have SLA's, lets go!!!

    // Go get some data from the device.
    $pingCtlResults = snmp_walk($device, "pingMIB.pingObjects.pingCtlTable.pingCtlEntry", "-OQUs", '+DISMAN-PING-MIB', $mibdir);
    $pingResults = snmp_walk($device, "pingMIB.pingObjects.pingResultsTable.pingResultsEntry", "-OQUs", '+DISMAN-PING-MIB', $mibdir);
    $jnxPingResults = snmp_walk($device, "jnxPingResultsEntry", "-OQUs", '+JUNIPER-PING-MIB', $mibdir);

    // Instanciate index foreach MIB to query field more easily
    $jnxPingResults_table = [];
    foreach (explode("\n", $jnxPingResults) as $line)
    {
        $key_val = explode(' ', $line, 3);

        $key = $key_val[0];
        $value = $key_val[2];

        // To get owner index and test name
        $prop_id = explode('.', $key);
        $property = $prop_id[0];
        $owner = $prop_id[1];
        $test = $prop_id[2];

        $jnxPingResults_table[$owner.".".$test][$property] = $value;
    }

    $pingResults_table = [];
    foreach (explode("\n", $pingResults) as $line)
    {
        $key_val = explode(' ', $line, 3);

        $key = $key_val[0];
        $value = $key_val[2];

        // To get owner index and test name
        $prop_id = explode('.', $key);
        $property = $prop_id[0];
        $owner = $prop_id[1];
        $test = $prop_id[2];

        $pingResults_table[$owner.".".$test][$property] = $value;
    }

    $pingCtlResults_table = [];
    foreach (explode("\n", $pingCtlResults) as $line)
    {
        $key_val = explode(' ', $line, 3);

        $key = $key_val[0];
        $value = $key_val[2];

        // To get owner index and test name
        $prop_id = explode('.', $key);
        $property = $prop_id[0];
        $owner = $prop_id[1];
        $test = $prop_id[2];

        $pingCtlResults_table[$owner.".".$test][$property] = $value;
    }

    // Get the needed informations
    $uptime = snmp_get($device, 'sysUpTime.0', '-Otv', 'SNMPv2-MIB');
    $time_offset = (time() - intval($uptime) / 100);

    
    foreach ($slas as $sla) {
        $sla_nr = $sla['sla_nr'];
        $rtt_type = $sla['rtt_type'];
        $owner = $sla['owner'];
        $test = $sla['tag'];

        // Lets process each SLA
        $time = fixdate($jnxPingResults_table[$owner . "." .$test]['jnxPingResultsTime']);
        $update = [];


        // Use DISMAN-PING Status codes.
        $opstatus = $pingCtlResults_table[$owner . "." .$test]['pingCtlRowStatus'];

        if ($opstatus == 'active') {
            $opstatus = 0;        // 0=Good
        } else {
            $opstatus = 2;        // 2=Critical
        }

        // Populating the update array means we need to update the DB.
        if ($opstatus != $sla['opstatus']) {
            $update['opstatus'] = $opstatus;
        }

        $rtt = $jnxPingResults_table[$owner . "." .$test]['jnxPingResultsRttUs'] / 1000;
        echo 'SLA : ' . $rtt_type . ' ' . $owner . ' ' . $test . '... ' . $rtt . 'ms at ' . $time . "\n";

        $fields = [
            'rtt' => $rtt,
        ];

        // The base RRD
        $rrd_name = ['sla', $sla_nr];
        $rrd_def = RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000);
        $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
        data_update($device, 'sla', $tags, $fields);

        // Let's gather some per-type fields.
        switch ($rtt_type) {
            case 'IcmpEcho':
            case 'IcmpTimeStamp':
                $icmp = [
                    'MinRttUs' => $jnxPingResults_table[$owner . "." .$test]['jnxPingResultsMinRttUs'] / 1000,
                    'MaxRttUs' => $jnxPingResults_table[$owner . "." .$test]['jnxPingResultsMaxRttUs'] / 1000,
                    'StdDevRttUs' => $pingResults_table[$owner . "." .$test]['jnxPingResultsStdDevRttUs'] / 1000,
                    // 'rtt_sense' => $pingResults_table[$owner . "." .$test]['jnxPingResults'],
                    'ProbeResponses' => $pingResults_table[$owner . "." .$test]['pingResultsProbeResponses'],
                    'ProbeLoss' => $pingResults_table[$owner . "." .$test]['pingResultsSentProbes'] - $pingResults_table[$owner . "." .$test]['pingResultsProbeResponses'],
                ];
                $rrd_name = ['sla', $sla_nr, $rtt_type];
                $rrd_def = RrdDefinition::make()
                    ->addDataset('MinRttUs', 'GAUGE', 0, 300000)
                    ->addDataset('MaxRttUs', 'GAUGE', 0, 300000)
                    ->addDataset('StdDevRttUs', 'GAUGE', 0, 300000)
                    ->addDataset('ProbeResponses', 'GAUGE', 0, 300000)
                    ->addDataset('ProbeLoss', 'GAUGE', 0, 300000);
                $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                data_update($device, 'sla', $tags, $icmp);
                $fields = array_merge($fields, $icmp);
                break;
        }

        d_echo('The following datasources were collected for #' . $sla['sla_nr'] . ":\n");
        d_echo($fields);
        

        // Update the DB if necessary
        if (count($update) > 0) {
            $updated = dbUpdate($update, 'slas', '`sla_id` = ?', [$sla['sla_id']]);
        }
    }
}

unset($slas);
