<?php

if (is_admin() === true || is_read() === true) {
    if ($vars['addsrv']) {
        if ($_SESSION['userlevel'] >= '10') {
            $updated = '1';

            $service_id = add_service($vars['device'], $vars['type'], $vars['descr'], $vars['ip'], $vars['params'], 0);
            if ($service_id) {
                $message       .= $message_break.'Service added ('.$service_id.')!';
                $message_break .= '<br />';
            }
        }
    }

    // Build the types list.
    if ($handle = opendir($config['nagios_plugins'])) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && !strstr($file, '.') && strstr($file, 'check_')) {
                list(,$check_name) = explode('_',$file,2);
                $servicesform .= "<option value='$check_name'>$check_name</option>";
            }
        }
        closedir($handle);
    }

    $dev         = device_by_id_cache($device['device_id']);
    $devicesform = "<option value='".$dev['device_id']."'>".$dev['hostname'].'</option>';

    if ($updated) {
        print_message('Device Settings Saved');
    }

    echo '<div class="col-sm-6">';

    include_once 'includes/print-service-add.inc.php';
}
else {
    include 'includes/error-no-perm.inc.php';
}
