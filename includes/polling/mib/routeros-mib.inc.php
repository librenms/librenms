<?php

echo "MIKROTIK-MIB ";

$ifIndex_array = explode("\n", snmp_walk($device, "ifIndex", "-Oqv", "IF-MIB"));

$snmp_get_oids = "";
foreach ($ifIndex_array as $ifIndex) {
        $snmp_get_oids .= "ifDescr.$ifIndex ifName.$ifIndex ifType.$ifIndex ";
}

$ifDescr_array = array();
$ifDescr_array = snmp_get_multi($device, $snmp_get_oids, '-OQU', 'IF-MIB');
d_echo($ifDescr_array);
foreach ($ifIndex_array as $ifIndex) {
    d_echo("\$ifDescr_array[$ifIndex]['IF-MIB::ifDescr'] = " . $ifDescr_array[$ifIndex]['IF-MIB::ifDescr'] . "\n");
    $ifDescr = $ifDescr_array[$ifIndex]['IF-MIB::ifDescr'];
    d_echo("\$ifDescr_array[$ifIndex]['IF-MIB::ifName'] = " . $ifDescr_array[$ifIndex]['IF-MIB::ifName'] . "\n");
    d_echo("\$ifDescr_array[$ifIndex]['IF-MIB::ifType'] = " . $ifDescr_array[$ifIndex]['IF-MIB::ifType'] . "\n");
    $ifType = $ifDescr_array[$ifIndex]['IF-MIB::ifType'];

    if ($ifType == 'ieee80211') {
        // $mib_oids     (oidindex,dsname,dsdescription,dstype)
        $mib_oids['mtxrWlApTxRate.'.$ifIndex] = array(
                    '',
                    'mtxrWlApTxRate',
                    'Transmit Rate',
                    'COUNTER',
                    );
        $mib_oids['mtxrWlApRxRate.'.$ifIndex] = array(
                    '',
                    'mtxrWlApRxRate',
                    'Receive Rate',
                    'COUNTER',
                    );
        $mib_oids['mtxrWlApClientCount.'.$ifIndex] = array(
                    '',
                    'mtxrWlApClientCount',
                    'Client Count',
                    'GAUGE',
                    );
        $mib_oids['mtxrWlApNoiseFloor.'.$ifIndex] = array(
                    '',
                    'mtxrWlApNoiseFloor',
                    'Noise Floor',
                    'GAUGE',
                    );
        $mib_oids['mtxrWlApOverallTxCCQ.'.$ifIndex] = array(
                    '',
                    'mtxrWlApOveralTxCCQ',
                    'Tx CCQ',
                    'GAUGE',
                    );
    }
}


$mib_graphs = array(
    'routeros_rate',
    'routeros_clients',
    'routeros_noisefloor',
    'routeros_txccq',
);


poll_mib_def($device, 'MIKROTIK-MIB:mikrotik-wifi', 'mikrotik', $mib_oids, $mib_graphs, $graphs);
// EOF
