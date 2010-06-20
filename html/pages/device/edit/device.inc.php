<?php

if($_POST['editing']) {
  if($_SESSION['userlevel'] > "7") {
    include("includes/device-edit.inc.php");
  }
}

$device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE `device_id` = '".$device['device_id']."'"));
$descr  = $device['purpose'];

if($updated && $update_message) {
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

$device_types = array('server','network','firewall','workstation','printer','power');

echo("<table cellpadding=0 cellspacing=0><tr><td>

<h5>
  <a href='?page=delhost&id=".$device['device_id']."'>
    <img src='images/16/server_delete.png' align='absmiddle'>
    Delete
  </a>
</h5>

<form id='edit' name='edit' method='post' action=''>
  <input type=hidden name='editing' value='yes'>
  <table width='400' border='0'>
    <tr>
      <td><div align='right'>Description</div></td>
      <td colspan='3'><input name='descr' size='32' value='" . $device['purpose'] . "'></input></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>SNMP Community</div></td>
      <td colspan='3'><input name='community' size='20' value='" . $device['community'] . "'></input>
      </td>
    </tr>
    <tr>
    <td><div align=right>SNMP Version</div></td>
    <td><select name='snmpver'>
          <option value='v1'>v1</option>
          <option value='v2c'" . ($device['snmpver'] == 'v2c' ? 'selected=selected' : '') . ">v2c</option>
        </select>
      </td>
    </tr>
    <tr>
      <td width='300'><div align='right'>SNMP Port</div></td>
      <td colspan='3'><input name='port' size='20' value='" . $device['port'] . "'></input>
      </td>
    </tr>

   <tr>
      <td align='right'>
        Type
      </td>
      <td>
        <select name='type'>");

$unknown = 1;
foreach ($device_types as $type)
{
  echo '          <option value="'.$type.'"';
  if ($device['type'] == $type)
  {
    echo 'selected="1"';
    $unknown = 0;
  }
  echo ' >' . ucfirst($type) . '</option>';
}
  if ($unknown)
  {
    echo '          <option value="other">Other</option>';
  }
echo("
        </select>
      </td>
    </tr>
    <tr>
      <td><div align='right'>Disable</div></td>
      <td><input name='disabled' type='checkbox' id='disabled' value='1'");
if($device['disabled']) { echo("checked=checked"); }
echo("/></td>
      <td><div align='right'>Ignore</div></td>
      <td><input name='ignore' type='checkbox' id='disable' value='1'");
      if($device['ignore']) { echo("checked=checked"); }
echo("/></td>
    </tr>");

echo('
  </table>
  <input type="submit" name="Submit" value="Save" />
  <label><br />
  </label>
</form>

</td>
<td width="50"></td><td></td></tr></table>');


?>
