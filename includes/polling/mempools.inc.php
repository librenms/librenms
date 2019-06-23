<?php

use LibreNMS\RRD\RrdDefinition;

foreach (dbFetchRows('SELECT * FROM mempools WHERE device_id = ?', array($device['device_id'])) as $mempool) {
    echo 'Mempool '.$mempool['mempool_descr'].': ';

    $mempool_type = $mempool['mempool_type'];
    $mempool_index = $mempool['mempool_index'];

    $file = \LibreNMS\Config::get('install_dir') . '/includes/polling/mempools/' . $mempool_type . '.inc.php';
    if (is_file($file)) {
        include $file;
    }

    if ($mempool['total']) {
        $percent = round(($mempool['used'] / $mempool['total'] * 100), 2);
    } else {
        $percent = 0;
    }

    echo $percent.'% ';

    $rrd_name = array('mempool', $mempool_type, $mempool_index);
    $rrd_def = RrdDefinition::make()
        ->addDataset('used', 'GAUGE', 0)
        ->addDataset('free', 'GAUGE', 0);

    $fields = array(
        'used' => $mempool['used'],
        'free' => $mempool['free'],
    );

    $tags = compact('mempool_type', 'mempool_index', 'rrd_name', 'rrd_def');
    data_update($device, 'mempool', $tags, $fields);

    $mempool['state'] = array(
                         'mempool_used'  => $mempool['used'],
                         'mempool_perc'  => $percent,
                         'mempool_free'  => $mempool['free'],
                         'mempool_total' => $mempool['total'],
                        );

    if (!empty($mempool['largestfree'])) {
        $mempool['state']['mempool_largestfree'] = set_numeric($mempool['largestfree']);
    }

    if (!empty($mempool['lowestfree'])) {
        $mempool['state']['mempool_lowestfree'] = set_numeric($mempool['lowestfree']);
    }

    dbUpdate($mempool['state'], 'mempools', '`mempool_id` = ?', array($mempool['mempool_id']));

    echo "\n";
}//end foreach
