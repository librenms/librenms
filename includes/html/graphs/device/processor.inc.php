<?php

$procs = dbFetchRows('SELECT * FROM `processors` where `device_id` = ?', [$device['device_id']]);

if (empty($procs)) {
    graph_text_and_exit($width, $height, 'No Processors', $vars);
}

if (\LibreNMS\Config::getOsSetting($device['os'], 'processor_stacked')) {
    include 'includes/html/graphs/device/processor_stack.inc.php';
} else {
    include 'includes/html/graphs/device/processor_separate.inc.php';
}
