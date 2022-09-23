<?php

use LibreNMS\RRD\RrdDefinition;

$diskio_data = dbFetchRows('SELECT * FROM `ucd_diskio` WHERE `device_id`  = ?', [$device['device_id']]);

if (count($diskio_data)) {
    $diskio_cache = [];
    $diskio_cache = snmpwalk_cache_oid($device, 'diskIOEntry', $diskio_cache, 'UCD-DISKIO-MIB');

    foreach ($diskio_data as $diskio) {
        $index = $diskio['diskio_index'];

        $entry = $diskio_cache[$index];

        echo $diskio['diskio_descr'] . ' ';

        d_echo($entry);

        $tags = [
            'rrd_name'  => ['ucd_diskio', $diskio['diskio_descr']],
            'rrd_def'   => RrdDefinition::make()
                ->addDataset('read', 'DERIVE', 0, 125000000000)
                ->addDataset('written', 'DERIVE', 0, 125000000000)
                ->addDataset('reads', 'DERIVE', 0, 125000000000)
                ->addDataset('writes', 'DERIVE', 0, 125000000000),
            'descr'     => $diskio['diskio_descr'],
        ];

        $fields = [
            'read'    => $entry['diskIONReadX'],
            'written' => $entry['diskIONWrittenX'],
            'reads'   => $entry['diskIOReads'],
            'writes'  => $entry['diskIOWrites'],
        ];

        data_update($device, 'ucd_diskio', $tags, $fields);
    }//end foreach

    echo "\n";
}//end if

unset($diskio_data);
unset($diskio_cache);
