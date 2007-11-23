<?php

if($_SESSION[userlevel] < '5') {
  print_error("Insufficient Privileges");
} else {

if($_POST['delsrv']) {
  if($_SESSION['userlevel'] > "5") {
    include("includes/del-srv.inc");
  }
}

$query = mysql_query("SELECT * FROM `services` AS S, `devices` AS D WHERE S.service_host = D.device_id ORDER BY hostname");
while($device = mysql_fetch_array($query)) {
  $servicesform .= "<option value='" . $device[service_id] . "'>" . $device['service_id'] .  "." . $device['hostname'] . " - " . $device['service_type'] .  "</option>";
}

if($updated) { print_message("Service Deleted!"); }

echo("
<h4>Delete Service</h4>
<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='delsrv' value='yes'>
  <table width='300' border='0'>
    <tr>
      <td>
        Device
      </td>
      <td>
        <select name='service'>
          $servicesform
        </select> 
      </td>
    </tr>
  </table>
<input type='submit' name='Submit' value='Delete' />
</form>");


}
?>
