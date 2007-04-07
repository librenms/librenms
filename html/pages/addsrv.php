<?php

if($_SESSION[userlevel] < '5') { 
  print_error("Insufficient Privileges");
} else {

if($_POST['addsrv']) {
  if($userlevel > "5") {
    include("includes/add-srv.inc");
  }
}

if ($handle = opendir($installdir . "/includes/services/")) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != ".." && !strstr($file, ".")) {
            $servicesform .= "<option value='$file'>$file</option>";
        }
    }
    closedir($handle);
}

$query = mysql_query("SELECT * FROM `devices` ORDER BY `hostname`");
while($device = mysql_fetch_array($query)) {
  $devicesform .= "<option value='" . $device[id] . "'>" . $device['hostname'] . "</option>";
}

if($updated) { print_message("Device Settings Saved"); }

echo("
<h4>Add Service</h4>
<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='addsrv' value='yes'>
  <table width='200' border='0'>
    <tr>
      <td>
        Device
      </td>
      <td>
        <select name='device'>
          $devicesform
        </select>
      </td>
    </tr>
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
    <tr>
      <td width='300'><div align='right'>Description</div></td>
      <td colspan='2'><textarea name='descr' cols='50'></textarea></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>IP Address</div></td>
      <td colspan='2'><input name='ip'></textarea></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Parameters</div></td>
      <td colspan='2'><input name='params'></textarea></td>
    </tr>
   <tr>
  </table>
  <input type='submit' name='Submit' value='Add' />
  <label><br />
  </label>
</form>");


}

