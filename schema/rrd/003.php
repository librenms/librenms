<?php

// rename old Cisco cpmCPU files
$rrd_operation = new \LibreNMS\RRD\RenameOp(
    'Cisco cpmCPU rename',
    'cpmCPU-[0-9]*',
    function ($file) {
        list(, $index) = explode('-', $file, 2);
        return array('processor', 'cpm', $index);
    }
);
