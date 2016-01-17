<?php

// XUPS-MIB
if ($device['os'] == 'powerware') {
    echo 'XUPS-MIB ';

    // XUPS-MIB::xupsBatVoltage.0 = INTEGER: 51
    $oids = snmp_walk($device, 'xupsBatVoltage', '-Osqn', 'XUPS-MIB');
    d_echo($oids."\n");

    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$descr) = explode(' ', $data, 2);
            $split_oid        = explode('.', $oid);
            $volt_id          = $split_oid[(count($split_oid) - 1)];
            $volt_oid         = ".1.3.6.1.4.1.534.1.2.2.$volt_id";
            $divisor          = 1;
            $volt             = (snmp_get($device, $volt_oid, '-O vq') / $divisor);
            $descr            = 'Battery'.(count(explode("\n", $oids)) == 1 ? '' : ' '.($volt_id + 1));
            $type             = 'xups';
            $index            = '1.2.5.'.$volt_id;

            discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $volt);
        }
    }

    // XUPS-MIB::xupsInputNumPhases.0 = INTEGER: 1
    $oids = trim(snmp_walk($device, 'xupsInputNumPhases', '-OsqnU', 'XUPS-MIB'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        // XUPS-MIB::xupsInputVoltage.1 = INTEGER: 228
        $volt_oid = ".1.3.6.1.4.1.534.1.3.4.1.2.$i";
        $descr    = 'Output';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $type    = 'xups';
        $divisor = 1;
        $current = (snmp_get($device, $volt_oid, '-Oqv') / $divisor);
        $index   = '3.4.1.2.'.$i;

        discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    // XUPS-MIB::xupsOutputNumPhases.0 = INTEGER: 1
    $oids = trim(snmp_walk($device, 'xupsOutputNumPhases', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        // XUPS-MIB::xupsOutputVoltage.1 = INTEGER: 228
        $volt_oid = ".1.3.6.1.4.1.534.1.4.4.1.2.$i";
        $descr    = 'Output';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $type    = 'xups';
        $divisor = 1;
        $current = (snmp_get($device, $volt_oid, '-Oqv') / $divisor);
        $index   = '4.4.1.2.'.$i;

        discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    // XUPS-MIB::xupsBypassNumPhases.0 = INTEGER: 1
    $oids = trim(snmp_walk($device, 'xupsBypassNumPhases', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $volt_oid = ".1.3.6.1.4.1.534.1.5.3.1.2.$i";
        $descr = 'Bypass';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $type    = 'xups';
        $divisor = 1;
        $current = (snmp_get($device, $volt_oid, '-Oqv') / $divisor);
        $index   = '5.3.1.2.'.$i;

        discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}//end if
