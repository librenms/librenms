<?php

// APC
if ($device['os'] == 'apc') {
    echo 'APC Load ';
    // UPS
    $three_phase = snmp_get($device, 'upsBasicOutputPhase.0', '-OsqvU', 'PowerNet-MIB');
    echo $three_phase;
    if ($three_phase == 3) {
        for ($ph_ld = 1; $ph_ld <= 3;) { // Input Current 3Phase
            $ph_load = snmp_get($device, 'upsPhaseOutputPercentLoad.1.1.'.$ph_ld, '-OsqvU', 'PowerNet-MIB');
            $phld_oid = '.1.3.6.1.4.1.318.1.1.1.9.3.3.1.10.1.1';

            discover_sensor($valid['sensor'], 'load', $device, $phld_oid.'.'.$ph_ld, $phld_oid.'.'.$ph_ld, apc, 'Phase '. $ph_ld .' Output Load', 1, 1, null, null, null, null, $ph_load);
            $ph_ld++;
        }
    } else {
        $oid_array = array(
            array(
                'HighPrecOid' => 'upsHighPrecOutputLoad',
                'AdvOid'      => 'upsAdvOutputLoad',
                'type'        => 'apc',
                'index'       => 0,
                'descr'       => 'Load',
                'divisor'     => 10,
                'mib'         => '+PowerNet-MIB',
            ),
        );
        foreach ($oid_array as $item) {
            $oids           = snmp_get($device, $item['HighPrecOid'].'.'.$item['index'], '-OsqnU', $item['mib']);
            if (empty($oids)) {
                $oids        = snmp_get($device, $item['AdvOid'].'.'.$item['index'], '-OsqnU', $item['mib']);
                $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.3';
                $current = $oids;
                $item['divisor'] = 1;
            } else {
                $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.3';
                $value = explode(" ", $oids);
                $current = $value[1]/$item['divisor'];
            }

            if (!empty($oids)) {
                d_echo($oids);

                $oids = trim($oids);
                if ($oids) {
                    echo $item['type'].' '.$item['mib'].' UPS';
                }

                discover_sensor($valid['sensor'], 'load', $device, $current_oid.'.'.$item['index'], $current_oid.'.'.$item['index'], $item['type'], $item['descr'], $item['divisor'], 1, null, null, null, null, $current);
            }
        }//end foreach
    }//end if
}
