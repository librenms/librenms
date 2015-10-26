<?php

// APC
if ($device['os'] == 'apc') {
    echo 'APC Load ';
    // UPS
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
        $low_limit      = null;
        $low_limit_warn = null;
        $warn_limit     = null;
        $high_limit     = null;
        $oids           = snmp_get($device, $item['HighPrecOid'].'.'.$item['index'], '-OsqnU', $item['mib']);
        if (empty($oids)) {
            $oids        = snmp_get($device, $item['AdvOid'].'.'.$item['index'], '-OsqnU', $item['mib']);
            $current_oid = $item['AdvOid'];
        }
        else {
            $current_oid = $item['HighPrecOid'];
        }

        if (!empty($oids)) {
            d_echo($oids);

            $oids = trim($oids);
            if ($oids) {
                echo $item['type'].' '.$item['mib'].' UPS';
            }

            if (stristr($current_oid, 'HighPrec')) {
                $current = ($oids / $item['divisor']);
            }
            else {
                $current         = $oids;
                $item['divisor'] = 1;
            }

            discover_sensor($valid['sensor'], 'load', $device, $current_oid.'.'.$item['index'], $current_oid.'.'.$item['index'], $item['type'], $item['descr'], $item['divisor'], 1, $low_limit, $low_limit_warn, $warn_limit, $high_limit, $current);
        }
    }//end foreach
}//end if
