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

    echo "<div class='row'>
            <div class='col-sm-6'>";

  require_once "includes/print-service-add.inc.php";

    echo "</div>
            </div>";


}

?>
