<?php

use LibreNMS\RRD\RrdDefinition;

$toner_data = dbFetchRows('SELECT * FROM toner WHERE device_id = ?', array($device['device_id']));

foreach ($toner_data as $toner) {
    echo 'Checking toner '.$toner['toner_descr'].'... ';

    $raw_toner = snmp_get($device, $toner['toner_oid'], '-OUqnv');
    $tonerperc = get_toner_levels($device, $raw_toner, $toner['toner_capacity']);
    echo $tonerperc." %\n";

    $tags = array(
        'rrd_def'     => RrdDefinition::make()->addDataset('toner', 'GAUGE', 0, 20000),
        'rrd_name'    => array('toner', $toner['toner_index']),
        'rrd_oldname' => array('toner', $toner['toner_descr']),
        'index'       => $toner['toner_index'],
    );
    data_update($device, 'toner', $tags, $tonerperc);

    // Log empty supplies (but only once)
    if ($tonerperc == 0 && $toner['toner_current'] > 0) {
        log_event('Toner ' . $toner['toner_descr'] . ' is empty', $device, 'toner', 5, $toner['toner_id']);
    }

    // Log toner swap
    if ($tonerperc > $toner['toner_current']) {
        log_event('Toner ' . $toner['toner_descr'] . ' was replaced (new level: ' . $tonerperc . '%)', $device, 'toner', 3, $toner['toner_id']);
    }

    dbUpdate(array('toner_current' => $tonerperc, 'toner_capacity' => $toner['toner_capacity']), 'toner', '`toner_id` = ?', array($toner['toner_id']));
}//end foreach
