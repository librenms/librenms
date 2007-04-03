<?php

if($_SESSION[userlevel] < '5') { 
  print_error("Insufficient Privileges");
} else {

if($_POST['editing']) {
  if($userlevel > "5") {
    include("includes/edit-host.inc");
  }
}

if($_POST['addsrv']) {
  if($userlevel > "5") {
    include("includes/add-srv.inc");
  }
}


$device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE `id` = '$_GET[id]'"));
$descr  = $device['purpose'];

if($updated) { print_message("Device Settings Saved"); }

if($device['type'] == 'server') { $server_select = "selected"; }
if($device['type'] == 'network') { $network_select = "selected"; }
if($device['type'] == 'firewall') { $firewall_select = "selected"; }
if($device['type'] == 'workstation') { $workstation_select = "selected"; }
if($device['type'] == 'other') { $other_select = "selected"; }

echo("<table cellpadding=0 cellspacing=0><tr><td>

<h4>Edit Device</h4>

<form id='edit' name='edit' method='post' action=''>
  <input type=hidden name='editing' value='yes'>
  <table width='200' border='0'>
    <tr>
      <td width='300'><div align='right'>Description</div></td>
      <td colspan='3'><textarea name='descr' cols='50'>$descr</textarea></td>
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
      <td width='300'><input name='disable' type='checkbox' id='disable' value='1'");
if($device['disabled']) {echo("checked=checked");}
echo("/></td>
      <td width='300'><div align='right'>Ignore</div></td>
      <td width='300'><input name='ignore' type='checkbox' id='disable' value='1'");
if($device['ignore']) {echo("checked=checked");}
echo("/></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Apache</div></td>
      <td width='300'><label>
        <input name='apache' type='checkbox' id='apache' value='1'");

if($device['apache']) {echo("checked=checked");}

echo("/>
      </label></td>
      <td width='300'><div align='right'>Temp</div></td>
      <td width='300'><input name='temp' type='checkbox' id='temp' value='1'");
if($device['temp']) {echo("checked=checked");}
echo("/></td>
    </tr>
  </table>
  <input type='submit' name='Submit' value='Save' />
  <label><br />
  </label>
</form></td>
<td width=50></td><td>");

$srvdir = $installdir . "/includes/services/";

if ($handle = opendir($srvdir)) {
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            $services[] = $file;
            $servicesform .= "<option value='$file'>$file</option>";
        }
    }
    closedir($handle);
}

echo("

<h4>Add Service</h4>

<form id='addsrv' name='addsrv' method='post' action=''>
  <input type=hidden name='addsrv' value='yes'>
  <table width='200' border='0'>
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

echo("</td></tr></table>");

}

