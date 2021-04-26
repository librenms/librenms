<?php

$oids = snmpwalk_cache_oid($device, 'cmdExcFrequency', [], 'RS-XX8000-DVB-TX-MIB');

echo 'Output-Frequency ';

$count = 1;
foreach ($oids as $id => $data) {
    $num_oid = '.1.3.6.1.4.1.2566.127.1.2.167.4.1.1.1.64.' . $count;
    $index = 'cmdExcFrequency.' . $id;
    $descr = (count($oids) > 1) ? 'Frequency ' . $id : 'Frequency';
    $type = 'rs';
    $current = $data['cmdExcFrequency'];
    discover_sensor($valid['sensor'], 'frequency', $device, $num_oid, $index, $type, $descr, '1', '1', null, null, null, null, $current);
    $count++;
}
