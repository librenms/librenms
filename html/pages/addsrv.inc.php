<?php

if ($_SESSION['userlevel'] < '10')
{
  include("includes/error-no-perm.inc.php");
}
else
{
  if ($_POST['addsrv'])
  {
    if ($_SESSION['userlevel'] >= '10')
    {
      $updated = '1';

      #FIXME should call add_service (needs more parameters)
      $service_id = dbInsert(array('device_id' => $_POST['device'], 'service_ip' => $_POST['ip'], 'service_type' => $_POST['type'], 'service_desc' => $_POST['descr'], 'service_param' => $_POST['params'], 'service_ignore' => '0'), 'services');

      if ($service_id)
      {
        $message .= $message_break . "Service added (".$service_id.")!";
        $message_break .= "<br />";
      }
    }
  }

  if ($handle = opendir($config['install_dir'] . "/includes/services/"))
  {
    while (false !== ($file = readdir($handle)))
    {
      if ($file != "." && $file != ".." && !strstr($file, "."))
      {
        $servicesform .= "<option value='$file'>$file</option>";
      }
    }
    closedir($handle);
  }

  foreach (dbFetchRows("SELECT * FROM `devices` ORDER BY `hostname`") as $device)
  {
    $devicesform .= "<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>";
  }

  if ($updated) { print_message("Device Settings Saved"); }

  $pagetitle[] = "Add service";

  echo("
<h4>Add Service</h4>
<form id='addsrv' name='addsrv' method='post' action='' class='form-horizontal' role='form'>
  <div class='well well-lg'>
    <div class='form-group'>
      <input type='hidden' name='addsrv' value='yes'>
      <label for='device' class='col-sm-2 control-label'>Device</label>
      <div class='col-sm-5'>
        <select name='device' class='form-control input-sm'>
          $devicesform
        </select>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='type' class='col-sm-2 control-label'>Type</label>
      <div class='col-sm-5'>
        <select name='type' id='type' class='form-control input-sm'>
          $servicesform
        </select>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='descr' class='col-sm-2 control-label'>Description</label>
      <div class='col-sm-5'>
        <textarea name='descr' id='descr' class='form-control input-sm' rows='5'></textarea>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='ip' class='col-sm-2 control-label'>IP Address</label>
      <div class='col-sm-5'>
        <input name='ip' id='ip' class='form-control input-sm' placeholder='IP Address'>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <div class='form-group'>
      <label for='params' class='col-sm-2 control-label'>Parameters</label>
      <div class='col-sm-5'>
        <input name='params' id='params' class='form-control input-sm'>
      </div>
      <div class='col-sm-5'>
      </div>
    </div>
    <button type='submit' name='Submit' class='btn btn-default input-sm'>Add Service</button>
  </div>
</form>");

}

?>
