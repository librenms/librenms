<?php

$oids = snmpwalk_cache_oid($device, 'cardTemperature', [], 'CADANT-CMTS-EQUIPMENT-MIB');
$oids = snmpwalk_cache_oid($device, 'cardName', $oids, 'CADANT-CMTS-EQUIPMENT-MIB');
$oids = snmpwalk_cache_oid($device, 'cardTemperatureHighWarn', $oids, 'CADANT-CMTS-EQUIPMENT-MIB');
$oids = snmpwalk_cache_oid($device, 'cardTemperatureHighError', $oids, 'CADANT-CMTS-EQUIPMENT-MIB');

foreach ($oids as $index => $entry) {
    $tempCurr = $entry['cardTemperature'];
    if ($tempCurr !== '999') {
        $temperature_oid = ".1.3.6.1.4.1.4998.1.1.10.1.4.2.1.29.$index";
        $descr = $entry['cardName'];
        $warnlimit = $entry['cardTemperatureHighWarn'];
        $limit = $entry['cardTemperatureHighError'];

        discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $index, 'cmts', $descr, '1', '1', null, null, $warnlimit, $limit, $tempCurr);
    }
}
