<?php

if (is_admin() === false && is_read() === false) {
    include 'includes/error-no-perm.inc.php';
}
else {
    $pagetitle[] = 'Edit service';

    if ($_POST['confirm-editsrv']) {
        if ($_SESSION['userlevel'] > '5') {
            include 'includes/service-edit.inc.php';
        }
    }

    foreach (dbFetchRows('SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id ORDER BY hostname') as $device) {
        $servicesform .= "<option value='".$device['service_id']."'>".$device['hostname'].' - '.$device['service_type'].'</option>';
    }

    if ($updated) {
        print_message('Service updated!');
    }

    if ($_POST['editsrv'] == 'yes') {
        include_once 'includes/print-service-edit.inc.php';
    }
    else {
        echo "
        <h4>Delete Service</h4>
        <form id='editsrv' name='editsrv' method='post' action='' class='form-horizontal' role='form'>
          <input type=hidden name='delsrv' value='yes'>
          <div class='well well-lg'>
            <div class='form-group'>
              <label for='service' class='col-sm-2 control-label'>Device - Service</label>
              <div class='col-sm-5'>
                <select name='service' id='service' class='form-control input-sm'>
                  $servicesform
                </select>
              </div>
              <div class='col-sm-5'>
              </div>
            </div>
            <button type='submit' name='editsrv' id='editsrv' value='yes' class='btn btn-primary input-sm'>Edit</button>
          </div>
        </form>";
    }//end if
}//end if
