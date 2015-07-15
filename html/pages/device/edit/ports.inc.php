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
<form id='ignoreport' name='ignoreport' method='post' action='' role='form' class='form-inline'>
  <input type=hidden name='ignoreport' value='yes'>
  <input type=hidden name=device value='".$device['device_id']."'>");

echo("<table class='table table-condensed table-responsive table-striped'>
  <tr>
                 <th>Index</th>
                 <th>Name</th>
                 <th>Admin</th>
                 <th>Oper</th>
                 <th>Disable</th>
                 <th>Ignore</th>
                 <th>Description</th>
</tr>
<tr>
    <td><button type='submit' value='Save' class='btn btn-success btn-sm' title='Save current port disable/ignore settings'/>Save</button><button type='submit' value='Reset' class='btn btn-danger btn-sm' id='form-reset' title='Reset form to previously-saved settings'/>Reset</button></td>
    <td></td>
    <td></td>
    <td><button type='submit' value='Alerted' class='btn btn-default btn-sm' id='alerted-toggle' title='Toggle alerting on all currently-alerted ports'/>Alerted</button><button type='submit' value='Down' class='btn btn-default btn-sm' id='down-select' title='Disable alerting on all currently-down ports'/>Down</button></td>
    <td><button type='submit' value='Toggle' class='btn btn-default btn-sm' id='disable-toggle' title='Toggle polling for all ports'/>Toggle</button><button type='submit' value='Select' class='btn btn-default btn-sm' id='disable-select' title='Disable polling on all ports'/>Select All</button></td>
    <td><button type='submit' value='Toggle' class='btn btn-default btn-sm' id='ignore-toggle' title='Toggle alerting for all ports'/>Toggle</button><button type='submit' value='Select' class='btn btn-default btn-sm' id='ignore-select' title='Disable alerting on all ports'/>Select All</button></td>
    <td></td>
</tr>
");
?>

<script>
    $('#disable-toggle').click(function(event) {
        // invert selection on all disable buttons
        event.preventDefault();
        $('input[name^="disabled_"]').trigger('click'); 
    });
    $('#ignore-toggle').click(function(event) {
        // invert selection on all ignore buttons
        event.preventDefault();
        $('input[name^="ignore_"]').trigger('click');
    });
    $('#disable-select').click(function(event) {
        // select all disable buttons
        event.preventDefault();
        $('.disable-check').prop('checked',true);
    });
    $('#ignore-select').click(function(event) {
        // select all ignore buttons
        event.preventDefault();
        $('.ignore-check').prop('checked',true);
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
                $('input[name="ignore_' + port_id + '"]').trigger('click');
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
                $('input[name="ignore_' + port_id + '"]').trigger('click');
            }
        });
    });
    $('#form-reset').click(function(event) {
        // reset objects in the form to their previous values
        event.preventDefault();
        $('#ignoreport')[0].reset();
    });
</script>

<?php

$row=1;

foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? ORDER BY `ifIndex` ", array($device['device_id'])) as $port)
{
  $port = ifLabel($port);

  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  echo("<tr>");
  echo("<td>". $port['ifIndex']."</td>");
  echo("<td>".$port['label'] . "</td>");
  echo("<td>". $port['ifAdminStatus']."</td>");

  # Mark interfaces which are OperDown (but not AdminDown) yet not ignored or disabled, or up yet ignored or disabled
  # - as to draw the attention to a possible problem.
  $isportbad = ($port['ifOperStatus'] == 'down' && $port['ifAdminStatus'] != 'down') ? 1 : 0;
  $dowecare  = ($port['ignore'] == 0 && $port['disabled'] == 0) ? $isportbad : !$isportbad;
  $outofsync = $dowecare ? " class='red'" : "";

  echo("<td><span name='operstatus_".$port['port_id']."'".$outofsync.">". $port['ifOperStatus']."</span></td>");

  echo("<td>");
  echo("<input type=checkbox class='disable-check' name='disabled_".$port['port_id']."'".($port['disabled'] ? 'checked' : '').">");
  echo("<input type=hidden name='olddis_".$port['port_id']."' value=".($port['disabled'] ? 1 : 0).">");
  echo("</td>");

  echo("<td>");
  echo("<input type=checkbox class='ignore-check' name='ignore_".$port['port_id']."'".($port['ignore'] ? 'checked' : '').">");
  echo("<input type=hidden name='oldign_".$port['port_id']."' value=".($port['ignore'] ? 1 : 0).">");
  echo("</td>");
  echo("<td>".$port['ifAlias'] . "</td>");

  echo("</tr>\n");

  $row++;
}

echo('</table>');
echo('</form>');
echo('</div>');

?>
