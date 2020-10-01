<?php

echo 'APC Load ';

$phasecount = $phasecount = $pre_cache['apcups_phase_count'];
if ($phasecount > 1) {
    $oids = snmpwalk_cache_oid($device, 'upsPhaseOutputPercentLoad', [], 'PowerNet-MIB');
    d_echo($oids);
    foreach ($oids as $index => $data) {
        $type = 'apcUPS';
        $descr = 'Phase ' . substr($index, -1);
        $load_oid = '.1.3.6.1.4.1.318.1.1.1.9.3.3.1.10.' . $index;
        $divisor = 1;
        $load = $data['upsPhaseOutputPercentLoad'];
        if ($load >= 0) {
            discover_sensor($valid['sensor'], 'load', $device, $load_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $load);
        }
    }
    unset($oids);
} else {
    $oid_array = [
        [
            'HighPrecOid' => 'upsHighPrecOutputLoad',
            'AdvOid'      => 'upsAdvOutputLoad',
            'type'        => 'apc',
            'index'       => 0,
            'descr'       => 'Load(VA)',
            'divisor'     => 10,
            'mib'         => '+PowerNet-MIB',
        ],
    ];
    foreach ($oid_array as $item) {
        $oids = snmp_get($device, $item['HighPrecOid'] . '.' . $item['index'], '-OsqnU', $item['mib']);
        if (empty($oids)) {
            $oids = snmp_get($device, $item['AdvOid'] . '.' . $item['index'], '-OsqnU', $item['mib']);
            $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.3';
            $current = $oids;
            $item['divisor'] = 1;
        } else {
            $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.3';
            $value = explode(' ', $oids);
            $current = $value[1] / $item['divisor'];
        }
        if (! empty($oids)) {
            d_echo($oids);
            $oids = trim($oids);
            if ($oids) {
                echo $item['type'] . ' ' . $item['mib'] . ' UPS';
            }
            discover_sensor($valid['sensor'], 'load', $device, $current_oid . '.' . $item['index'], $current_oid . '.' . $item['index'], $item['type'], $item['descr'], $item['divisor'], 1, null, null, null, null, $current);
        }
    }//end foreach
}
