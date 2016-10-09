<?php

$procs = dbFetchRows('SELECT * FROM `processors` where `device_id` = ?', array($device['device_id']));

if ($config['os'][$device['os']]['processor_stacked'] == 1) {
    include 'includes/graphs/device/processor_stack.inc.php';
} else {
    include 'includes/graphs/device/processor_separate.inc.php';
}
