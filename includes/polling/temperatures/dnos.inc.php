<?php

if ($device['os'] == 'dnos'){
    $temps = snmp_walk($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.5', '-Osqn');
    

    $counter = 0;
    foreach (explode("\n", $temps) as $i => $t) {
        $t   = explode(' ',$t);
        $oid = $t[0];
        $val = $t[1];
        
        
        if (substr($oid, -1) == '1') {
            $counter = $counter + 1;
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $counter, 'dnos',
                'Unit '.$counter.' CPU temperature', '1', '1', null, null, null, null, $val);
        }
    }
}
