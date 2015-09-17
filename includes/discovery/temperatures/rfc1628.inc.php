<?php
// RFC1628 UPS
if (isset($config['modules_compat']['rfc1628'][$device['os']]) && $config['modules_compat']['rfc1628'][$device['os']]) {
    $oids = snmp_walk($device, '1.3.6.1.2.1.33.1.2.7', '-Osqn', 'UPS-MIB');
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'RFC1628 Battery Temperature ';
    }

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $temperature_id   = $split_oid[(count($split_oid) - 1)];
            $temperature_oid  = "1.3.6.1.2.1.33.1.2.7.$temperature_id";
            $temperature      = snmp_get($device, $temperature_oid, '-Ovq');
            $descr            = 'Battery'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($temperature_id + 1));

            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'rfc1628', $descr, '1', '1', null, null, null, null, $temperature);
        }
    }
}//end if
