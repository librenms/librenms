<?php

$diskio_data = dbFetchRows('SELECT * FROM `ucd_diskio` WHERE `device_id`  = ?', array($device['device_id']));

if (count($diskio_data)) {
    $diskio_cache = array();
    $diskio_cache = snmpwalk_cache_oid($device, 'diskIOEntry', $diskio_cache, 'UCD-DISKIO-MIB');

    echo 'Checking UCD DiskIO MIB: ';

    foreach ($diskio_data as $diskio) {
        $index = $diskio['diskio_index'];

        $entry = $diskio_cache[$index];

        echo $diskio['diskio_descr'].' ';

        d_echo($entry);

        $tags = array(
            'rrd_name'  => array('ucd_diskio', $diskio['diskio_descr']),
            'rrd_def'   => array(
                'DS:read:DERIVE:600:0:125000000000',
                'DS:written:DERIVE:600:0:125000000000',
                'DS:reads:DERIVE:600:0:125000000000',
                'DS:writes:DERIVE:600:0:125000000000',
            ),
            'descr'     => $diskio['diskio_descr'],
        );

        $fields = array(
            'read'    => $entry['diskIONReadX'],
            'written' => $entry['diskIONWrittenX'],
            'reads'   => $entry['diskIOReads'],
            'writes'  => $entry['diskIOWrites'],
        );

        data_update($device, 'ucd_diskio', $tags, $fields);

    }//end foreach

    echo "\n";
}//end if

unset($diskio_data);
unset($diskio_cache);
