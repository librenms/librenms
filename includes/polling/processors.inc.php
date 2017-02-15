<?php

foreach (dbFetchRows('SELECT * FROM processors WHERE device_id = ?', array($device['device_id'])) as $processor) {
    echo 'Processor '.$processor['processor_descr'].'... ';

    $processor_type = $processor['processor_type'];
    $processor_index = $processor['processor_index'];

    $file = $config['install_dir'].'/includes/polling/processors/'. $processor_type .'.inc.php';
    if (is_file($file)) {
        include $file;
    } else {
        $proc = snmp_get($device, $processor['processor_oid'], '-O Uqnv', '""');
    }

    $rrd_name = array('processor', $processor_type, $processor_index);
    $rrd_def = 'DS:usage:GAUGE:600:-273:1000';

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

    $tags = compact('processor_type', 'processor_index', 'rrd_name', 'rrd_def');
    data_update($device, 'processors', $tags, $fields);

    dbUpdate(array('processor_usage' => $proc), 'processors', '`processor_id` = ?', array($processor['processor_id']));
}//end foreach

unset($processor);
