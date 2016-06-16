<?php

if ($_SESSION['userlevel'] < '10') {
    include 'includes/error-no-perm.inc.php';
}
else {
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

    foreach (scandir($config['nagios_plugins']) as $file) {
        if (substr($file, 0, 6) === 'check_') {
            $check_name = substr($file, 6);
            $servicesform .= "<option value='$check_name'>$check_name</option>";
        }
    }

    foreach (dbFetchRows('SELECT * FROM `devices` ORDER BY `hostname`') as $device) {
        $devicesform .= "<option value='".$device['device_id']."'>".$device['hostname'].'</option>';
    }

    if ($updated) {
        print_message('Device Settings Saved');
    }

    $pagetitle[] = 'Add service';

    echo "<div class='row'>
        <div class='col-sm-6'>";

    include_once 'includes/print-service-add.inc.php';

    echo '</div>
        </div>';
}//end if
