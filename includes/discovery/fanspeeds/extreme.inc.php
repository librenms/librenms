<?php

if ($device['os'] == 'xos') {
    // Function used to round limit values to hundreds to avoid h/w/l limits being changed on every discovery as a change of 1rpm for fan speed would cause the limit values to change since they're dynamically calculated
    function roundme($val = 0) {
        $diff = $val % 100;
        if ($diff >= 50) {
            $ret = $val + (100-$diff);
        } else {
            $ret = $val - $diff;
        }
        return $ret;
    }

    echo(" EXTREME-BASE-MIB ");

    // Fan Speed
    $oid = "1.3.6.1.4.1.1916.1.1.1.9.1.4";
    $oids = snmpwalk_cache_multi_oid($device, $oid, array(), "EXTREME-BASE-MIB");

    foreach ($oids as $index => $entry) {
	// fix index to a proper int
        preg_match('/^.*\.([^\.]*)$/', "$index", $matches);
        $index = $matches[1];
        // substract 100 from index to start from 1 instead of 101
        $modindex = ($index - 100);
        $oid	= "1.3.6.1.4.1.1916.1.1.1.9.1.4.$index";
        $value	= snmp_get($device, $oid, '-Oqv', 'EXTREME-BASE-MIB');
        $descr	= "Fan Speed $modindex";
        $high_limit		= roundme($value * 1.5);
        $high_warn_limit	= roundme($value * 1.25);
        $low_warn_limit		= roundme($value * 0.75);
        $low_limit		= roundme($value * 0.5);
        if (is_numeric($value)) {
            discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'extreme-fanspeed', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);
        }
    }
}

// EOF
