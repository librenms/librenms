<?php
echo 'ZXAN ONT Rx Signal Level';

$multiplier = 1;
$divisor = 1000;

$gpon_id['gpon-olt_1/1/1'] = 285278465;
$gpon_id['gpon-olt_1/1/2'] = 285278466;
$gpon_id['gpon-olt_1/1/3'] = 285278467;
$gpon_id['gpon-olt_1/1/4'] = 285278468;
$gpon_id['gpon-olt_1/1/5'] = 285278469;
$gpon_id['gpon-olt_1/1/6'] = 285278470;
$gpon_id['gpon-olt_1/1/7'] = 285278471;
$gpon_id['gpon-olt_1/1/8'] = 285278472;
$gpon_id['gpon-olt_1/1/9'] = 285278473;
$gpon_id['gpon-olt_1/1/10'] = 285278474;
$gpon_id['gpon-olt_1/1/11'] = 285278475;
$gpon_id['gpon-olt_1/1/12'] = 285278476;
$gpon_id['gpon-olt_1/1/13'] = 285278477;
$gpon_id['gpon-olt_1/1/14'] = 285278478;
$gpon_id['gpon-olt_1/1/15'] = 285278479;
$gpon_id['gpon-olt_1/1/16'] = 285278480;
$gpon_id['gpon-olt_1/2/1'] = 285278721;
$gpon_id['gpon-olt_1/2/2'] = 285278722;
$gpon_id['gpon-olt_1/2/3'] = 285278723;
$gpon_id['gpon-olt_1/2/4'] = 285278724;
$gpon_id['gpon-olt_1/2/5'] = 285278725;
$gpon_id['gpon-olt_1/2/6'] = 285278726;
$gpon_id['gpon-olt_1/2/7'] = 285278727;
$gpon_id['gpon-olt_1/2/8'] = 285278728;
$gpon_id['gpon-olt_1/2/9'] = 285278729;
$gpon_id['gpon-olt_1/2/10'] = 285278730;
$gpon_id['gpon-olt_1/2/11'] = 285278731;
$gpon_id['gpon-olt_1/2/12'] = 285278732;
$gpon_id['gpon-olt_1/2/13'] = 285278733;
$gpon_id['gpon-olt_1/2/14'] = 285278734;
$gpon_id['gpon-olt_1/2/15'] = 285278735;
$gpon_id['gpon-olt_1/2/16'] = 285278736;


$gpon_onu_id['gpon-onu_1/1/1'] = 285278465;
$gpon_onu_id['gpon-onu_1/1/2'] = 285278466;
$gpon_onu_id['gpon-onu_1/1/3'] = 285278467;
$gpon_onu_id['gpon-onu_1/1/4'] = 285278468;
$gpon_onu_id['gpon-onu_1/1/5'] = 285278469;
$gpon_onu_id['gpon-onu_1/1/6'] = 285278470;
$gpon_onu_id['gpon-onu_1/1/7'] = 285278471;
$gpon_onu_id['gpon-onu_1/1/8'] = 285278472;
$gpon_onu_id['gpon-onu_1/1/9'] = 285278473;
$gpon_onu_id['gpon-onu_1/1/10'] = 285278474;
$gpon_onu_id['gpon-onu_1/1/11'] = 285278475;
$gpon_onu_id['gpon-onu_1/1/12'] = 285278476;
$gpon_onu_id['gpon-onu_1/1/13'] = 285278477;
$gpon_onu_id['gpon-onu_1/1/14'] = 285278478;
$gpon_onu_id['gpon-onu_1/1/15'] = 285278479;
$gpon_onu_id['gpon-onu_1/1/16'] = 285278480;
$gpon_onu_id['gpon-onu_1/2/1'] = 285278721;
$gpon_onu_id['gpon-onu_1/2/2'] = 285278722;
$gpon_onu_id['gpon-onu_1/2/3'] = 285278723;
$gpon_onu_id['gpon-onu_1/2/4'] = 285278724;
$gpon_onu_id['gpon-onu_1/2/5'] = 285278725;
$gpon_onu_id['gpon-onu_1/2/6'] = 285278726;
$gpon_onu_id['gpon-onu_1/2/7'] = 285278727;
$gpon_onu_id['gpon-onu_1/2/8'] = 285278728;
$gpon_onu_id['gpon-onu_1/2/9'] = 285278729;
$gpon_onu_id['gpon-onu_1/2/10'] = 285278730;
$gpon_onu_id['gpon-onu_1/2/11'] = 285278731;
$gpon_onu_id['gpon-onu_1/2/12'] = 285278732;
$gpon_onu_id['gpon-onu_1/2/13'] = 285278733;
$gpon_onu_id['gpon-onu_1/2/14'] = 285278734;
$gpon_onu_id['gpon-onu_1/2/15'] = 285278735;
$gpon_onu_id['gpon-onu_1/2/16'] = 285278736;


foreach ($pre_cache['zxa10_oids'] as $index => $entry)
{
    // OLT RX Signal Strength
    if ($entry['zxAnOpticalIfRxPwrCurrValue'])
    {

        $i = explode('.', $index);
        list($dec, $onu_num) = $i;
        foreach ($gpon_id as $key => $value) if ($dec == $value) $port = $key;

        $descr = $port . ':' . $onu_num;
        $currentsignal = $entry['zxAnOpticalIfRxPwrCurrValue'];
        if($currentsignal == 2147483647) continue;
        // Discover Sensor
        $group = str_replace('gpon-olt_', '', $port) . ' RX Signal';

        discover_sensor($valid['sensor'], 'dbm', $device, '.1.3.6.1.4.1.3902.1082.30.40.2.4.1.2.' . $index, 'zxAnOpticalIfRxPwrCurrValue.' . $index, 'zxa10_olt_from_onu_rx', $descr, $divisor, $multiplier, '-34', '-30', '-10', '-8', $currentsignal, 'snmp', null, null, null, $group);
    }
}

foreach ($pre_cache['zxa10_onu_oids'] as $index => $entry)
{
    // ONU RX Signal Strength
    if ($entry['zxAnPonRxOpticalPower'])
    {
        $i = explode('.', $index);
        list($dec, $onu_num) = $i;
        foreach ($gpon_onu_id as $key => $value) if ($dec == $value) $port = $key;

        $descr = $port . ':' . $onu_num;
        $currentsignal = $entry['zxAnPonRxOpticalPower'];

        if ($currentsignal == -80000) $signal = -40000;

        // Discover Sensor
        $group = str_replace('gpon-onu_', '', $port) . ' RX Signal';

        discover_sensor($valid['sensor'], 'dbm', $device, '.1.3.6.1.4.1.3902.1082.500.1.2.4.2.1.2.' . $index, 'zxAnPonRxOpticalPower.' . $index, 'zxa10_onu_from_olt_rx', $descr, $divisor, $multiplier, '-34', '-32', '-10', '-8', $signal, 'snmp', null, null, null, $group);
    }
}

