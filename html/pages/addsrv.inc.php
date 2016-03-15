<?php

if ($_SESSION['userlevel'] < '10') {
    include 'includes/error-no-perm.inc.php';
}
else {
    if ($_POST['addsrv']) {
        if ($_SESSION['userlevel'] >= '10') {
            $updated = '1';

            $service_id = service_add($_POST['device'], $_POST['type'], $_POST['descr'], $_POST['ip'], $_POST['params'], 0);
            if ($service_id) {
                $message       .= $message_break.'Service added ('.$service_id.')!';
                $message_break .= '<br />';
            }
        }
    }

    if ($handle = opendir($config['nagios_plugins'])) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && !strstr($file, '.') && strstr($file, 'check_')) {
                list(,$check_name) = explode('_',$file,2);
                $servicesform .= "<option value='$check_name'>$check_name</option>";
            }
        }

        closedir($handle);
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
