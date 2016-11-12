<?php

// rename old style hr Processor RRD files
$rrd_operation = new \LibreNMS\RRD\RenameOp(
    'hrProcessor rename',
    'hrProcessor-[0-9]*',
    function ($file) {
        list(, $index) = explode('-', $file, 2);
        return array('processor', 'hr', $index);
    }
);
