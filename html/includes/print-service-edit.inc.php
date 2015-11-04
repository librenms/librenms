<?php

if (isset($_POST['service']) && is_numeric($_POST['service'])) {
    $service = dbFetchRow('SELECT * FROM `services` WHERE `service_id`=?', array($_POST['service']));

    echo "
<h3><span class='label label-primary threeqtr-width'>Edit Service</span></h3>
<form id='confirm-editsrv' name='confirm-editsrv' method='post' action='' class='form-horizontal' role='form'>
  <input type='hidden' name='device' value='".$service['device_id']."'>
  <input type='hidden' name='service' value='".$service['service_id']."'>
  <div class='well well-lg'>
    <div class='form-group'>
      <label for='descr' class='col-sm-2 control-label'>Description</label>
      <div class='col-sm-5'>
        <textarea name='descr' id='descr' class='form-control input-sm' rows='5'>".$service['service_desc']."</textarea>
      </div>
    </div>
    <div class='form-group'>
      <label for='ip' class='col-sm-2 control-label'>IP Address</label>
      <div class='col-sm-5'>
        <input name='ip' id='ip' value='".$service['service_ip']."' class='form-control input-sm' placeholder='IP Address'>
      </div>
    </div>
    <div class='form-group'>
      <label for='params' class='col-sm-2 control-label'>Parameters</label>
      <div class='col-sm-5'>
        <input name='params' id='params' value='".$service['service_param']."' class='form-control input-sm'>
      </div>
      <div class='col-sm-5'>
          This may be required based on the service check.
      </div>
    </div>
    <button type='submit' id='confirm-editsrv' name='confirm-editsrv' value='yes' class='btn btn-primary input-sm'>Edit Service</button>
  </div>
</form>";
}//end if
