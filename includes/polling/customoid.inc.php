<?php

use LibreNMS\RRD\RrdDefinition;

foreach (dbFetchRows('SELECT * FROM `customoids` WHERE `customoid_passed` = 1 AND `device_id` = ?', [$device['device_id']]) as $customoid) {
    d_echo($customoid);

    $prev_oid_value = $customoid['customoid_current'];

    $rawdata = snmp_get($device, $customoid['customoid_oid'], '-Oqv');

    $user_funcs = [
        'celsius_to_fahrenheit',
        'fahrenheit_to_celsius',
        'uw_to_dbm',
    ];

    if (is_numeric($rawdata)) {
        $os->enableGraph('customoid');
        $oid_value = $rawdata;
    } else {
        $oid_value = 0;
        $error = 'Invalid SNMP reply.';
    }

    if ($customoid['customoid_divisor'] && $oid_value !== 0) {
        $oid_value = ($oid_value / $customoid['customoid_divisor']);
    }
    if ($customoid['customoid_multiplier']) {
        $oid_value = ($oid_value * $customoid['customoid_multiplier']);
    }

    if (isset($customoid['user_func']) && in_array($customoid['user_func'], $user_funcs)) {
        $oid_value = $customoid['user_func']($oid_value);
    }

    echo 'Custom OID ' . $customoid['customoid_descr'] . ': ';
    echo $oid_value . ' ' . $customoid['customoid_unit'] . "\n";

    $fields = [
        'oid_value' => $oid_value,
    ];

    $rrd_name = ['customoid', $customoid['customoid_descr']];
    if ($customoid['customoid_datatype'] == 'COUNTER') {
        $datatype = $customoid['customoid_datatype'];
    } else {
        $datatype = 'GAUGE';
    }
    $rrd_def = RrdDefinition::make()
        ->addDataset('oid_value', $datatype);

    $tags = compact('rrd_name', 'rrd_def');

    data_update($device, 'customoid', $tags, $fields);
    dbUpdate(['customoid_current' => $oid_value, 'lastupdate' => ['NOW()'], 'customoid_prev' => $prev_oid_value], 'customoids', '`customoid_id` = ?', [$customoid['customoid_id']]);
}//end foreach

unset($customoid, $prev_oid_value, $rawdata, $user_funcs, $oid_value, $error, $fields, $rrd_def, $rrd_name, $tags);
