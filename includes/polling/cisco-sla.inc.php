<?php

$uptime      = snmp_get($device, 'sysUpTime.0', '-Otv');
$time_offset = (time() - intval($uptime) / 100);

$slavals = snmp_walk($device, 'ciscoRttMonMIB.ciscoRttMonObjects.rttMonCtrl.rttMonLatestRttOperTable', '-OUsqt', '+CISCO-RTTMON-MIB');

$sla_table = array();
foreach (explode("\n", $slavals) as $sla) {
    $key_val = explode(' ', $sla, 2);
    if (count($key_val) != 2) {
        $key_val[] = '';
    }

    $key   = $key_val[0];
    $value = $key_val[1];

    $prop_id = explode('.', $key);
    if ((count($prop_id) != 2) || !ctype_digit($prop_id[1])) {
        continue;
    }

    $property = str_replace('rttMonLatestRttOper', '', $prop_id[0]);
    $id       = intval($prop_id[1]);

    $sla_table[$id][$property] = trim($value);
}

// Update timestamps
foreach ($sla_table as &$sla) {
    $sla['UnixTime'] = intval(($sla['Time'] / 100 + $time_offset));
    $sla['TimeStr']  = strftime('%Y-%m-%d %H:%M:%S', $sla['UnixTime']);
}

unset($sla);

foreach (dbFetchRows('SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0 AND `status` = 1', array($device['device_id'])) as $sla) {
    echo 'SLA '.$sla['sla_nr'].': '.$sla['rtt_type'].' '.$sla['owner'].' '.$sla['tag'].'... ';

    $slarrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('sla-'.$sla['sla_nr'].'.rrd');

    if (!is_file($slarrd)) {
        rrdtool_create(
            $slarrd,
            '--step 300 
     DS:rtt:GAUGE:600:0:300000 '.$config['rrd_rra']
        );
    }

    if (isset($sla_table[$sla['sla_nr']])) {
        $slaval = $sla_table[$sla['sla_nr']];
        echo $slaval['CompletionTime'].'ms at '.$slaval['TimeStr'];
        $val = $slaval['CompletionTime'];
    }
    else {
        echo 'NaN';
        $val = 'U';
    }

    $fields = array(
        'rtt' => $val,
    );

    rrdtool_update($slarrd, $fields);
    echo "\n";
}//end foreach
