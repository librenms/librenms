<?php

echo('<div style="padding: 10px;">');

if($_POST['addsrv']) {
  if($_SESSION['userlevel'] == '10') {
    include("includes/service-add.inc.php");
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

$query = mysql_query("SELECT * FROM `devices` ORDER BY `hostname`");
while($device = mysql_fetch_array($query)) {
  $devicesform .= "<option value='" . $device['device_id'] . "'>" . $device['hostname'] . "</option>";
}

if($updated) { print_message("Device Settings Saved"); }


echo('<div style="float: left;">');

if(mysql_result(mysql_query("SELECT COUNT(*) from `services` WHERE `device_id` = '".$device['device_id']."'"), 0) > '0') {
   $i = "1";
   $service_query = mysql_query("select * from services WHERE device_id = '".$device['device_id']."' ORDER BY service_type");
   while($service = mysql_fetch_array($service_query)) {
     $existform .= "<option value='" . $service['service_id'] . "'>" . $service['service_type'] . "</option>";
     
   }

}

echo("
<form id='delsrv' name='delsrv' method='post' action=''>
  <input type=hidden name='delsrv' value='yes'>
  <table width='200' border='0'>
        <option type=hidden name=device value='".$device['device_id']."'>
    <tr>
      <td>
        Type
      </td>
      <td>
        <select name='type'>
          $existform
        </select>
      </td>
    </tr>
  </table>
  <input type='submit' name='Submit' value='Delete' />
  <label><br />
  </label>
</form>");


echo('</div>');

echo('<div style="width: 45%; float: right;">');

echo("
<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='addsrv' value='yes'>
  <table width='200' border='0'>
        <option type=hidden name=device value='".$device['device_id']."'>
    <tr>
      <td>
        Type
      </td>
      <td>
        <select name='type'>
          $servicesform
        </select>
      </td>
    </tr>
  </table>
  <input type='submit' name='Submit' value='Add' />
  <label><br />
  </label>
</form>");

echo('</div>');

echo('</div>');

?>
