<?php

if ($_SESSION['userlevel'] < '5')
{
  include("includes/error-no-perm.inc.php");
} else {

  $pagetitle[] = "Delete service";

  if ($_POST['delsrv'])
  {
    if ($_SESSION['userlevel'] > "5")
    {
      include("includes/service-delete.inc.php");
    }
  }

  foreach (dbFetchRows("SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id ORDER BY hostname") as $device)
  {
    $servicesform .= "<option value='" . $device['service_id'] . "'>" . $device['service_id'] .  "." . $device['hostname'] . " - " . $device['service_type'] .  "</option>";
  }

  if ($updated) { print_message("Service Deleted!"); }

  echo("
<h4>Delete Service</h4>
<form id='addsrv' name='addsrv' method='post' action='' class='form-horizontal' role='form'>
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
    <button type='submit' name='Submit' class='btn btn-default input-sm'>Delete</button>
  </div>
</form>");

}

?>
