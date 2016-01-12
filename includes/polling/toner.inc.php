<?php

if ($config['enable_printers']) {
    $toner_data = dbFetchRows('SELECT * FROM toner WHERE device_id = ?', array($device['device_id']));

    foreach ($toner_data as $toner) {
        echo 'Checking toner '.$toner['toner_descr'].'... ';

        if ($toner['toner_capacity_oid']) {
            // FIXME this if can go on 1-Sep-2012
            $toner['toner_capacity'] = snmp_get($device, $toner['toner_capacity_oid'], '-OUqnv');
        }

        $tonerperc = round((snmp_get($device, $toner['toner_oid'], '-OUqnv') / $toner['toner_capacity'] * 100));
        echo $tonerperc." %\n";

        $tags = array(
            'rrd_def'     => 'DS:toner:GAUGE:600:0:20000',
            'rrd_name'    => array('toner', $toner['toner_index']),
            'rrd_oldname' => array('toner', $toner['toner_descr']),
            'index'       => $toner['toner_index'],
        );
        data_update($device, 'toner', $tags, $tonerperc);

        // FIXME should report for toner out... :)
        // Log toner swap
        if ($tonerperc > $toner['toner_current']) {
            log_event('Toner '.$toner['toner_descr'].' was replaced (new level: '.$tonerperc.'%)', $device, 'toner', $toner['toner_id']);
        }

        dbUpdate(array('toner_current' => $tonerperc, 'toner_capacity' => $toner['toner_capacity']), 'toner', '`toner_id` = ?', array($toner['toner_id']));
    }//end foreach
}//end if
