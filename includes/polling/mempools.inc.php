<?php

foreach (dbFetchRows('SELECT * FROM mempools WHERE device_id = ?', array($device['device_id'])) as $mempool) {
    echo 'Mempool '.$mempool['mempool_descr'].': ';

    $mempool_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('mempool-'.$mempool['mempool_type'].'-'.$mempool['mempool_index'].'.rrd');

    $file = $config['install_dir'].'/includes/polling/mempools/'.$mempool['mempool_type'].'.inc.php';
    if (is_file($file)) {
        include $file;
    }
    else {
        // FIXME Do we need a generic mempool poller?
    }

    if ($mempool['total']) {
        $percent = round(($mempool['used'] / $mempool['total'] * 100), 2);
    }
    else {
        $percent = 0;
    }

    echo $percent.'% ';

    if (!is_file($mempool_rrd)) {
        rrdtool_create($mempool_rrd, '--step 300 DS:used:GAUGE:600:0:U DS:free:GAUGE:600:0:U '.$config['rrd_rra']);
    }

    $fields = array(
        'used' => $mempool['used'],
        'free' => $mempool['free'],
    );
    rrdtool_update($mempool_rrd, $fields);

    $tags = array('mempool_type' => $mempool['mempool_type'], 'mempool_index' => $mempool['mempool_index']);
    influx_update($device,'mempool',$tags,$fields);

    $mempool['state'] = array(
                         'mempool_used'  => $mempool['used'],
                         'mempool_perc'  => $percent,
                         'mempool_free'  => $mempool['free'],
                         'mempool_total' => $mempool['total'],
                        );

    if (!empty($mempool['largestfree'])) {
        $mempool['state']['mempool_largestfree'] = $mempool['largestfree'];
    }

    if (!empty($mempool['lowestfree'])) {
        $mempool['state']['mempool_lowestfree'] = $mempool['lowestfree'];
    }

    dbUpdate($mempool['state'], 'mempools', '`mempool_id` = ?', array($mempool['mempool_id']));

    echo "\n";
}//end foreach

unset($mempool_cache);
