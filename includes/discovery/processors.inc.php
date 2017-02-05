<?php

// Include all discovery modules
$include_dir = 'includes/discovery/processors';
require 'includes/include-dir.inc.php';

// Last-resort discovery here
require 'processors-ucd-old.inc.php';

// Remove processors which weren't redetected here
$sql = "SELECT * FROM `processors` WHERE `device_id`  = '".$device['device_id']."'";

d_echo($valid['processor']);

foreach (dbFetchRows($sql) as $test_processor) {
    $processor_index = $test_processor['processor_index'];
    $processor_type  = $test_processor['processor_type'];
    d_echo($processor_index.' -> '.$processor_type."\n");

    if (!$valid['processor'][$processor_type][$processor_index]) {
        echo '-';
        dbDelete('processors', '`processor_id` = ?', array($test_processor['processor_id']));
        log_event('Processor removed: type '.$processor_type.' index '.$processor_index.' descr '.$test_processor['processor_descr'], $device, 'processor', $test_processor['processor_id']);
    }

    unset($processor_oid);
    unset($processor_type);
}

echo "\n";

unset(
    $sql
);
