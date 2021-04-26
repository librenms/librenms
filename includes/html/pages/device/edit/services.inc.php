<?php

if (Auth::user()->hasGlobalRead()) {
    if ($vars['addsrv']) {
        if (Auth::user()->hasGlobalAdmin()) {
            $updated = '1';

            $service_id = add_service($vars['device'], $vars['type'], $vars['descr'], $vars['ip'], $vars['params'], $vars['ignore'], $vars['disabled'], 0, $vars['name']);
            if ($service_id) {
                $message .= $message_break . 'Service added (' . $service_id . ')!';
                $message_break .= '<br />';
            }
        }
    }

    // Build the types list.
    foreach (scandir(\LibreNMS\Config::get('nagios_plugins')) as $file) {
        if (substr($file, 0, 6) === 'check_') {
            $check_name = substr($file, 6);
            $servicesform .= "<option value='$check_name'>$check_name</option>";
        }
    }

    $dev = device_by_id_cache($device['device_id']);
    $devicesform = "<option value='" . $dev['device_id'] . "'>" . $dev['hostname'] . '</option>';

    if ($updated) {
        print_message('Device Settings Saved');
    }

    echo '<div class="col-sm-6">';

    include_once 'includes/html/print-service-add.inc.php';
} else {
    include 'includes/html/error-no-perm.inc.php';
}
