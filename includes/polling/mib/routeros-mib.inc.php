<?php

echo "MIKROTIK-MIB ";

$ifIndex_array = explode("\n", snmp_walk($device, "ifType", "-Oq", "IF-MIB"));

$mib_graphs = array(
    'routeros_rate',
    'routeros_clients',
    'routeros_noisefloor',
    'routeros_txccq',
);

d_echo($ifIndex_array);
foreach ($ifIndex_array as $ifResult) {
    list($oid, $ifType) = preg_split('/ /', $ifResult);
    if ($ifType == 'ieee80211') {
        list($junk, $ifIndex) = preg_split('/\./', $oid);
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

        poll_mib_def($device, 'MIKROTIK-MIB:mikrotik-wifi'.$ifIndex, 'mikrotik', $mib_oids, $mib_graphs, $graphs);
        unset($mib_oids);
    }
}

// EOF
