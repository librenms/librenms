<?php

// rename old style hr Processor RRD files
$renamer = new \LibreNMS\RrdRenamer(
    'hrProcessor rename',
    'hrProcessor-[0-9]*',
    function ($file) {
        list(, $index) = explode('-', $file, 2);
        return array('processor', 'hr', $index);
    }
);
