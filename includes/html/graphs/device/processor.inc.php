<?php

$procs = dbFetchRows('SELECT * FROM `processors` where `device_id` = ?', array($device['device_id']));

if (\LibreNMS\Config::get("os.{$device['os']}.processor_stacked")) {
    include 'includes/html/graphs/device/processor_stack.inc.php';
} else {
    include 'includes/html/graphs/device/processor_separate.inc.php';
}
