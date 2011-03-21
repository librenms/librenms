<?php

echo('<div style="padding: 10px;">');

if ($_POST['ignoreport'])
{
  if ($_SESSION['userlevel'] == '10')
  {
    include("includes/port-edit.inc.php");
  }
}

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

echo("<div style='float: left;'>
<form id='ignoreport' name='ignoreport' method='post' action=''>
  <input type=hidden name='ignoreport' value='yes'>
  <input type=hidden name=device value='".$device['device_id']."'>
<table>
<tr><th>Port</th><th>ifDescr</th><th>ifAdminStatus</th><th>ifOperStatus</th><th>Disable</th><th>Ignore</th></tr>
");

$query = mysql_query("SELECT * FROM `ports` WHERE device_id='".$device['device_id']."' ORDER BY `ifIndex` ");
while ($device = mysql_fetch_array($query))
{
  echo("<tr>");
  echo("<td align=right>". $device['ifIndex']."</td>");
  echo("<td align=left>".$device['ifDescr'] . "</td>");
  echo("<td align=right>". $device['ifAdminStatus']."</td>");

  # Mark interfaces which are OperDown (but not AdminDown) yet not ignored or disabled, or up yet ignored or disabled
  # - as to draw the attention to a possible problem.
  $isportbad = ($device['ifOperStatus'] == 'down' && $device['ifAdminStatus'] != 'down') ? 1 : 0;
  $dowecare  = ($device['ignore'] == 0 && $device['disabled'] == 0) ? 1 : 0;
  $outofsync = ($isportbad && $dowecare) ? "class=red" : "";

  echo("<td align=right><span ".$outofsync.">". $device['ifOperStatus']."</span></td>");

  echo("<td>");
  echo("<input type=checkbox name='disabled_".$device['interface_id']."'".($device['disabled'] ? 'checked' : '').">");
  echo("<input type=hidden name='olddis_".$device['interface_id']."' value=".($device['disabled'] ? 1 : 0).">");
  echo("</td>");

  echo("<td>");
  echo("<input type=checkbox name='ignore_".$device['interface_id']."'".($device['ignore'] ? 'checked' : '').">");
  echo("<input type=hidden name='oldign_".$device['interface_id']."' value=".($device['ignore'] ? 1 : 0).">");
  echo("</td>");

  echo("</tr>
");
}

echo('<tr><td></td><td></td><td></td><td></td><td><input type="submit" value="Save"></td></tr>');
echo('</table>');
echo('</form>');
echo('</div>');

?>
