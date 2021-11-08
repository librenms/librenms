<?php

header('Content-type: text/plain');

// FUA

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$device['device_id'] = $_POST['device_id'];
$module = 'discover_' . $_POST['discovery_module'];

if (! isset($module) && validate_device_id($device['device_id']) === false) {
    echo 'error with data';
    exit;
} else {
    if ($_POST['state'] == 'true') {
        $state = 1;
    } elseif ($_POST['state'] == 'false') {
        $state = 0;
    } else {
        $state = 0;
    }

    set_dev_attrib($device, $module, $state);
}
