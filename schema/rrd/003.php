<?php

// rename old Cisco cpmCPU files
$renamer = new \LibreNMS\RrdRenamer(
    'Cisco cpmCPU rename',
    'cpmCPU-[0-9]*',
    function ($file) {
        list(, $index) = explode('-', $file, 2);
        return array('processor', 'cpm', $index);
    }
);
