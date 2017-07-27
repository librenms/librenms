<?php

if ($device['os'] == 'avaya-ers') {
    
    foreach (explode("\n", $procs) as $i => $t) {
        $t   = explode(' ', $t);
        $oid = $t[0];
        $val = $t[1];
        discover_processor($valid['processor'], $device, $oid, zeropad($i + 1), 'avaya-ers', 'Unit '.($i + 1).' processor', '1', $val, $i, null);
    }
}
