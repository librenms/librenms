<?php

if ($device['os_group'] == 'unix') {
    // FIXME snmp_walk
    // ObserverNMS-style temperature
    $cmd = $config['snmpwalk'].' -M '.$config['mibdir'].' -M '.$config['mibdir'].' -m SNMPv2-SMI -Osqn -CI '.snmp_gen_auth($device).' '.$device['transport'].':'.$device['hostname'].':'.$device['port']." .1.3.6.1.4.1.2021.7891 | sed s/.1.3.6.1.4.1.2021.7891.// | grep '.1.1 ' | grep -v '.101.' | cut -d'.' -f 1";
    d_echo($cmd."\n");

    $oids = shell_exec($cmd);
    $oids = trim($oids);
    if ($oids) {
        echo 'Observer-Style ';
    }

    foreach (explode("\n", $oids) as $oid) {
        $oid = trim($oid);
        if ($oid != '') {
            // FIXME snmp_get
            $descr_query = $config['snmpget'].' -M '.$config['mibdir'].' -m SNMPv2-SMI -Osqn '.snmp_gen_auth($device).' '.$device['transport'].':'.$device['hostname'].':'.$device['port']." .1.3.6.1.4.1.2021.7891.$oid.2.1 | sed s/.1.3.6.1.4.1.2021.7891.$oid.2.1\ //";
            $descr       = trim(str_replace('"', '', shell_exec($descr_query)));
            $fulloid     = ".1.3.6.1.4.1.2021.7891.$oid.101.1";
            discover_sensor($valid['sensor'], 'temperature', $device, $fulloid, $oid, 'observium', $descr, '1', '1', null, null, null, null, $current);
        }
    }
}//end if
