<?php

if($_SESSION['userlevel'] < '5') { 
  print_error("Insufficient Privileges");
} else {

if($_POST['editing']) {
  if($_SESSION['userlevel'] > "5") {
    include("includes/edit-host.inc");
  }
}

$device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '$_GET[id]'"));
$descr  = $device['purpose'];

if($updated && $update_message) { 
  print_message($update_message); 
} elseif ($update_message) {
  print_error($update_message);
}

if($device['type'] == 'server') { $server_select = "selected"; }
if($device['type'] == 'network') { $network_select = "selected"; }
if($device['type'] == 'firewall') { $firewall_select = "selected"; }
if($device['type'] == 'workstation') { $workstation_select = "selected"; }
if($device['type'] == 'other') { $other_select = "selected"; }

echo("<table cellpadding=0 cellspacing=0><tr><td>

<h4>Edit Device</h4>

<h5>
  <a href='?page=delhost&id=".$device['device_id']."'>
    <img src='images/16/server_delete.png' align='absmiddle'>
    Delete
  </a>
</h5>

<form id='edit' name='edit' method='post' action=''>
  <input type=hidden name='editing' value='yes'>
  <table width='200' border='0'>
    <tr>
      <td width='300'><div align='right'>Description</div></td>
      <td colspan='3'><input name='descr' size='32' value='$descr'></input></td>
    </tr>
   <tr>
      <td>
        Type
      </td> 
      <td>
        <select name='type'>
          <option value='server' $server_select>Server</option>
          <option value='network' $network_select>Network</option>
          <option value='firewall' $firewall_select>Firewall</option>
          <option value='workstation' $workstation_select>Workstation</option>
          <option value='other' $other_select>Other</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Disable</div></td>
      <td width='300'><input name='disabled' type='checkbox' id='disabled' value='1'");
if($device['disabled']) { echo("checked=checked"); }
echo("/></td>
      <td width='300'><div align='right'>Ignore</div></td>
      <td width='300'><input name='ignore' type='checkbox' id='disable' value='1'");
      if($device['ignore']) { echo("checked=checked"); }
echo("/></td>
    </tr>");

echo("
  </table>
  <input type='submit' name='Submit' value='Save' />
  <label><br />
  </label>
</form>

</td>
<td width=50></td><td>");

  echo("</td></tr></table>");

}

