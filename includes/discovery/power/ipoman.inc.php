<?php

// IPOMANII-MIB
if ($device['os'] == 'ipoman') {
    echo ' IPOMANII-MIB ';

    // Inlet Disabled due to the fact thats it's Kwh instead of just Watt
    if (!is_array($cache['ipoman'])) {
        echo 'outletConfigDesc ';
        $cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, 'outletConfigDesc', $cache['ipoman']['out'], 'IPOMANII-MIB');
        echo 'outletConfigLocation ';
        $cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, 'outletConfigLocation', $cache['ipoman']['out'], 'IPOMANII-MIB');
        // echo("inletConfigDesc ");
        // $cache['ipoman']['in'] = snmpwalk_cache_multi_oid($device, "inletConfigDesc", $cache['ipoman'], "IPOMANII-MIB");
    }

    // $oids_in = array();
    $oids_out = array();

    // echo("inletStatusWH ");
    // $oids_in = snmpwalk_cache_multi_oid($device, "inletStatusWH", $oids_in, "IPOMANII-MIB");
    echo 'outletStatusWH ';
    $oids_out = snmpwalk_cache_multi_oid($device, 'outletStatusWH', $oids_out, 'IPOMANII-MIB');

    // if (is_array($oids_in))
    // {
    // foreach ($oids_in as $index => $entry)
    // {
    // $cur_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.5.' . $index;
    // $divisor = 10;
    // $descr = (trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') != '' ? trim($cache['ipoman']['in'][$index]['inletConfigDesc'],'"') : "Inlet $index");
    // $power = $entry['inletStatusWH'] / $divisor;
    //
    // discover_sensor($valid['sensor'], 'power', $device, $cur_oid, '1.3.1.3.'.$index, 'ipoman', $descr, $divisor, '1', NULL, NULL, NULL, NULL, $power);
    // // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    // }
    // }
    if (is_array($oids_out)) {
        foreach ($oids_out as $index => $entry) {
            $cur_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.5.'.$index;
            $divisor = 10;
            $descr   = (trim($cache['ipoman']['out'][$index]['outletConfigDesc'], '"') != '' ? trim($cache['ipoman']['out'][$index]['outletConfigDesc'], '"') : "Output $index");
            $power   = ($entry['outletStatusWH'] / $divisor);

            discover_sensor($valid['sensor'], 'power', $device, $cur_oid, '2.3.1.3.'.$index, 'ipoman', $descr, $divisor, '1', null, null, null, null, $power);
        }
    }
}//end if
