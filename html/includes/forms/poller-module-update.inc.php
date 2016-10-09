<?php
header('Content-type: text/plain');

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

// FUA
$device['device_id'] = $_POST['device_id'];
$module              = 'poll_'.$_POST['poller_module'];

if (!isset($module) && validate_device_id($device['device_id']) === false) {
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

    if (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] != $config['poller_modules'][$module]) {
        del_dev_attrib($device, $module);
    } else {
        set_dev_attrib($device, $module, $state);
    }
}
