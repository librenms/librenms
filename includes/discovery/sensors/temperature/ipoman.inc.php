<?php

/*
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

// pre-cache
$oidsEnv = SnmpQuery::cache()->enumStrings()->hideMib()->walk([
    'IPOMANII-MIB::ipmEnvEmd',
])->table(1);

// FIXME: EMD "stack" support?
// FIXME: What to do with IPOMANII-MIB::ipmEnvEmdConfigTempOffset.0 ?
if ($oidsEnv[0]['ipmEnvEmdStatusEmdType'] != 'disabled') {
    $descr = $oidsEnv[0]['ipmEnvEmdConfigTempName'];
    $value = $oidsEnv[0]['ipmEnvEmdStatusTemperature'] / 10;
    $high_limit = $oidsEnv[0]['ipmEnvEmdConfigTempHighSetPoint'];
    $low_limit = $oidsEnv[0]['ipmEnvEmdConfigTempLowSetPoint'];

    if ($descr != '' && is_numeric($value) && $value > '0') {
        $oid = '.1.3.6.1.4.1.2468.1.4.2.1.5.1.1.2.0';
        $descr = trim(str_replace('"', '', $descr));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'temperature',
            'sensor_oid' => $oid,
            'sensor_index' => 1,
            'sensor_type' => 'ipoman',
            'sensor_descr' => $descr,
            'sensor_divisor' => 10,
            'sensor_multiplier' => 1,
            'sensor_limit_low' => $low_limit,
            'sensor_limit_low_warn' => null,
            'sensor_limit_warn' => null,
            'sensor_limit' => $high_limit,
            'sensor_current' => $value,
            'entPhysicalIndex' => null,
            'entPhysicalIndex_measured' => null,
            'user_func' => null,
            'group' => null,
        ]));
    }
}
