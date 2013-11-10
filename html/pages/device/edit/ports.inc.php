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
                 <th width=100>Index</th>
                 <th width=100>Name</th>
                 <th width=50>Admin</th>
                 <th width=150>Oper</th>
                 <th width=150>Disable</th>
                 <th width=150>Ignore</th>
                 <th>Description</th>
</tr>
<tr align=center>
    <td><input type='submit' value='Save' title='Save current port disable/ignore settings'/><input type='submit' value='Reset' id='form-reset' title='Reset form to previously-saved settings'/></td>
    <td></td>
    <td></td>
    <td><input type='submit' value='Alerted' id='alerted-toggle' title='Toggle alerting on all currently-alerted ports'/><input type='submit' value='Down' id='down-select' title='Disable alerting on all currently-down ports'/></td>
    <td><input type='submit' value='Toggle' id='disable-toggle' title='Toggle polling for all ports'/><input type='submit' value='Select' id='disable-select' title='Disable polling on all ports'/></td>
    <td><input type='submit' value='Toggle' id='ignore-toggle' title='Toggle alerting for all ports'/><input type='submit' value='Select' id='ignore-select' title='Disable alerting on all ports'/></td>
    <td></td>
</tr>
");
?>

<script>
$(document).ready(function() {
    $('#disable-toggle').click(function(event) {
        // invert selection on all disable buttons
        event.preventDefault();
        $('[name^="disabled_"]').check('toggle');
    });
    $('#ignore-toggle').click(function(event) {
        // invert selection on all ignore buttons
        event.preventDefault();
        $('[name^="ignore_"]').check('toggle');
    });
    $('#disable-select').click(function(event) {
        // select all disable buttons
        event.preventDefault();
        $('[name^="disabled_"]').check();
    });
    $('#ignore-select').click(function(event) {
        // select all ignore buttons
        event.preventDefault();
        $('[name^="ignore_"]').check();
    });
    $('#down-select').click(function(event) {
        // select ignore buttons for all ports which are down
        event.preventDefault();
        $('[name^="operstatus_"]').each(function() {
            var name = $(this).attr('name');
            var text = $(this).text();
            if (name && text == 'down') {
                // get the interface number from the object name
                var port_id = name.split('_')[1];
                // find its corresponding checkbox and toggle it
                $('[name="ignore_' + port_id + '"]').check();
            }
        });
    });
    $('#alerted-toggle').click(function(event) {
        // toggle ignore buttons for all ports which are in class red
        event.preventDefault();
        $('.red').each(function() {
            var name = $(this).attr('name');
            if (name) {
                // get the interface number from the object name
                var port_id = name.split('_')[1];
                // find its corresponding checkbox and toggle it
                $('[name="ignore_' + port_id + '"]').check('toggle');
            }
        });
    });
    $('#form-reset').click(function(event) {
        // reset objects in the form to their previous values
        event.preventDefault();
        $('#ignoreport')[0].reset();
    });
});
</script>

<?php

$row=1;

foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? ORDER BY `ifIndex` ", array($device['device_id'])) as $port)
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
  $outofsync = $dowecare ? " class='red'" : "";

  echo("<td align=right><span name='operstatus_".$port['port_id']."'".$outofsync.">". $port['ifOperStatus']."</span></td>");

  echo("<td align=center>");
  echo("<input type=checkbox name='disabled_".$port['port_id']."'".($port['disabled'] ? 'checked' : '').">");
  echo("<input type=hidden name='olddis_".$port['port_id']."' value=".($port['disabled'] ? 1 : 0).">");
  echo("</td>");

  echo("<td align=center>");
  echo("<input type=checkbox name='ignore_".$port['port_id']."'".($port['ignore'] ? 'checked' : '').">");
  echo("<input type=hidden name='oldign_".$port['port_id']."' value=".($port['ignore'] ? 1 : 0).">");
  echo("</td>");
  echo("<td align=left>".$port['ifAlias'] . "</td>");

  echo("</tr>\n");

  $row++;
}

echo('</table>');
echo('</form>');
echo('</div>');

?>
