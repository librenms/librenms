<?php

if (is_admin() === TRUE || is_read() === TRUE) {

    if ($_POST['addsrv']) {
        if ($_SESSION['userlevel'] >= '10') {
            include("includes/service-add.inc.php");
        }
    }

    if ($_POST['delsrv']) {
        if ($_SESSION['userlevel'] >= '10') {
            include("includes/service-delete.inc.php");
        }
    }

    if ($_POST['confirm-editsrv']) {
        echo "yeah";
        if ($_SESSION['userlevel'] >= '10') {
            include("includes/service-edit.inc.php");
        }
    }

    if ($handle = opendir($config['install_dir'] . "/includes/services/")) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && !strstr($file, ".")) {
                $servicesform .= "<option value='$file'>$file</option>";
            }
        }

        closedir($handle);
    }

    $dev = device_by_id_cache($device['device_id']);
    $devicesform = "<option value='" . $dev['device_id'] . "'>" . $dev['hostname'] . "</option>";

    if ($updated) {
        print_message("Device Settings Saved");
    }

    if (dbFetchCell("SELECT COUNT(*) from `services` WHERE `device_id` = ?", array($device['device_id'])) > '0') {
        $i = "1";
        foreach (dbFetchRows("select * from services WHERE device_id = ? ORDER BY service_type", array($device['device_id'])) as $service) {
            $existform .= "<option value='" . $service['service_id'] . "'>" . $service['service_type'] . "</option>";
        }
    }

    echo '<div class="row">';

    if ($existform) {
        echo '<div class="col-sm-6">';
        if ($_POST['editsrv'] == "yes") {
            include_once "includes/print-service-edit.inc.php";
        } else {
            echo "
            <h3><span class='label label-info threeqtr-width'>Edit / Delete Service</span></h3>
            <form method='post' action='' class='form-horizontal'>
                <div class='well well-lg'>
                    <div class='form-group'>
                        <label for='service' class='col-sm-2 control-label'>Type: </label>
                        <div class='col-sm-4'>
                            <select name='service' class='form-control input-sm'>
                                $existform
                            </select>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-4'>
                            <button type='submit' class='btn btn-primary btn-sm' name='editsrv' id='editsrv' value='yes'>Edit</button> <button type='submit' class='btn btn-danger btn-sm' name='delsrv' id='delsrv' value='yes'>Delete</button>
                        </div>
                    </div>
                </div>
            </form>";
        }

        echo '</div>';
    }

    echo '<div class="col-sm-6">';

    require_once "includes/print-service-add.inc.php";

} else {
    include("includes/error-no-perm.inc.php");
}