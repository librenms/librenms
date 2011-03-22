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

echo("<div style='float: left; width: 100%'>
<form id='ignoreport' name='ignoreport' method='post' action=''>
  <input type=hidden name='ignoreport' value='yes'>
  <input type=hidden name=device value='".$device['device_id']."'>");

echo("<table cellpadding=3 cellspacing=0 width=100%>
  <tr align=center>
                 <th width=75>Index</th>
                 <th width=150>Name</th>
                 <th width=50>Admin</th>
                 <th width=50>Oper</th>
                 <th width=50>Disable</th>
                 <th width=50>Ignore</th>
                 <th>Description</th>

</tr>
");

$row=1;

$query = mysql_query("SELECT * FROM `ports` WHERE device_id='".$device['device_id']."' ORDER BY `ifIndex` ");
while ($port = mysql_fetch_array($query))
{

  $port = ifLabel($port);

  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr bgcolor=$row_colour>");
  echo("<td align=center>". $port['ifIndex']."</td>");
  echo("<td align=left>".$port['label'] . "</td>");
  echo("<td align=right>". $port['ifAdminStatus']."</td>");

  # Mark interfaces which are OperDown (but not AdminDown) yet not ignored or disabled, or up yet ignored or disabled
  # - as to draw the attention to a possible problem.
  $isportbad = ($port['ifOperStatus'] == 'down' && $port['ifAdminStatus'] != 'down') ? 1 : 0;
  $dowecare  = ($port['ignore'] == 0 && $port['disabled'] == 0) ? $isportbad : !$isportbad;
  $outofsync = $dowecare ? "class=red" : "";

  echo("<td align=right><span ".$outofsync.">". $port['ifOperStatus']."</span></td>");

  echo("<td align=center>");
  echo("<input type=checkbox name='disabled_".$port['interface_id']."'".($port['disabled'] ? 'checked' : '').">");
  echo("<input type=hidden name='olddis_".$port['interface_id']."' value=".($port['disabled'] ? 1 : 0).">");
  echo("</td>");

  echo("<td align=center>");
  echo("<input type=checkbox name='ignore_".$port['interface_id']."'".($port['ignore'] ? 'checked' : '').">");
  echo("<input type=hidden name='oldign_".$port['interface_id']."' value=".($port['ignore'] ? 1 : 0).">");
  echo("</td>");
  echo("<td align=left>".$port['ifAlias'] . "</td>");

  echo("</tr>
");

  $row++;

}

echo('<tr><td></td><td></td><td></td><td></td><td><input type="submit" value="Save"></td></tr>');
echo('</table>');
echo('</form>');
echo('</div>');

?>
