<?php

foreach (dbFetchRows('SELECT * FROM processors WHERE device_id = ?', array($device['device_id'])) as $processor) {
    echo 'Processor '.$processor['processor_descr'].'... ';

    $file = $config['install_dir'].'/includes/polling/processors/'.$processor['processor_type'].'.inc.php';
    if (is_file($file)) {
        include $file;
    }
    else {
        $proc = snmp_get($device, $processor['processor_oid'], '-O Uqnv', '""');
    }

    $procrrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('processor-'.$processor['processor_type'].'-'.$processor['processor_index'].'.rrd');

    if (!is_file($procrrd)) {
        rrdtool_create(
            $procrrd,
            '--step 300 
            DS:usage:GAUGE:600:-273:1000 '.$config['rrd_rra']
        );
    }

    $proc       = trim(str_replace('"', '', $proc));
    list($proc) = preg_split('@\ @', $proc);

    if (!$processor['processor_precision']) {
        $processor['processor_precision'] = '1';
    };
    $proc = round(($proc / $processor['processor_precision']), 2);

    echo $proc."%\n";

    $fields = array(
        'usage' => $proc,
    );

    rrdtool_update($procrrd, $fields);

    dbUpdate(array('processor_usage' => $proc), 'processors', '`processor_id` = ?', array($processor['processor_id']));
}//end foreach
