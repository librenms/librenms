<?php
$client_oids = snmpwalk_cache_oid($device, 'ubntStaEntry', [], 'UBNT-AirMAX-MIB');

/*
 * "ubntStaMac" => "00:00:00:00:00:00"
    "ubntStaName" => "name"
    "ubntStaSignal" => "-50"
    "ubntStaNoiseFloor" => "-91"
    "ubntStaDistance" => "450"
    "ubntStaCcq" => "333"
    "ubntStaAmp" => "2"
    "ubntStaAmq" => "0"
    "ubntStaAmc" => "0"
    "ubntStaLastIp" => "1.1.1.1"
    "ubntStaTxRate" => "173300000"
    "ubntStaRxRate" => "173300000"
    "ubntStaTxBytes" => "308058985654"
    "ubntStaRxBytes" => "31973828404"
    "ubntStaConnTime" => "20:5:34:18.00"
    "ubntStaLocalCINR" => "33"
    "ubntStaTxCapacity" => "149760"
    "ubntStaRxCapacity" => "148200"
    "ubntStaTxAirtime" => "1"
    "ubntStaRxAirtime" => "13"
    "ubntStaTxLatency" => "1"

*/
$type = 'airos';
foreach ($client_oids as $index => $entry) {
    $dec = [];
    $mac_explode = explode(':', $entry["ubntStaMac"]);
    foreach ($mac_explode as $one) {
        $dec[] = hexdec($one);
    }
    $indexDec = implode('.', $dec);

    discover_sensor(
        $valid['sensor'],
        'signal',
        $device,
        ".1.3.6.1.4.1.41112.1.4.7.1.3.1." . $indexDec,
        $indexDec,
        $type,
        $entry["ubntStaName"],
        1,
        '1',
        null,
        null,
        null,
        null,
        $entry["ubntStaSignal"],

    );
}
