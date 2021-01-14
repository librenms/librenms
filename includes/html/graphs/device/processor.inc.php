<?php

$procs = dbFetchRows('SELECT * FROM `processors` where `device_id` = ?', [$device['device_id']]);

if (Config::get('webui.graph_processor_last_values')) {
    $use_last_values = true;
} else {
    $use_last_values = false;
}

if (\LibreNMS\Config::getOsSetting($device['os'], 'processor_stacked')) {
    include 'includes/html/graphs/device/processor_stack.inc.php';
} else {
    include 'includes/html/graphs/device/processor_separate.inc.php';
}
