<?php

include("includes/javascript-interfacepicker.inc.php");

### This needs more verification. Is it already added? Does it exist?

echo("

<h3>Bill Properties</h3>

<form id='edit' name='edit' method='post' action=''>
  <input type=hidden name='action' value='update_bill'>
  <table width='400' border='0'>
    <tr>
      <td><div align='right'>Name</div></td>
      <td colspan='3'><input name='bill_name' size='32' value='" . $bill_data['bill_name'] . "'></input></td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Billing Day</div></td>
      <td colspan='3'><input name='bill_day' size='20' value='" . $bill_data['bill_day'] . "'></input>
      </td>
    </tr>
    <tr>
      <td width='300'><div align='right'>Monthly Quota</div></td>
      <td colspan='3'><input name='bill_gb' size='20' value='" . $bill_data['bill_gb'] . "'></input>GB
      </td>
    </tr>
    <tr>
      <td width='300'><div align='right'>CDR with 95th</div></td>
      <td colspan='3'><input name='bill_cdr' size='20' value='" . $bill_data['bill_cdr'] . "'></input>Kbps
      </td>
    </tr>
    <tr>
      <td align='right'>
        Type
      </td>
      <td>
        <select name='bill_type'>");

$bill_data_types = array ('cdr' => 'CDR with 95th', 'quota' => 'Monthly Quota');

$unknown = 1;
foreach ($bill_data_types as $type => $text)
{
  echo('          <option value="'.$type.'"');
  if ($bill_data['bill_type'] == $type)
  {
    echo('selected="1"');
    $unknown = 0;
  }
  echo(' >' . ucfirst($text) . '</option>');
}
echo("
        </select>
      </td>
    </tr>");

echo('
  </table>
  <input type="submit" name="Submit" value="Save" />
</form>
');

echo("<hr />");

$ports = dbFetchRows("SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
                      WHERE B.bill_id = ? AND P.interface_id = B.port_id
                      AND D.device_id = P.device_id", array($bill_data['bill_id']));

if (is_array($ports))
{
  echo("<h3>Billed Ports</h3>");

  echo("<table cellpadding=5 cellspacing=0>");
  foreach ($ports as $port)
  {
    if ($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }
    echo("<tr style=\"background-color: $bg\">");
    echo("<td>");
    echo(generate_device_link($port) . " - " . generate_port_link($port));
    if ($port['ifAlias']) { echo(" - " . $port['ifAlias']); }
    echo("</td><td>");
    echo("<form action='' method='post'><input type='hidden' name='action' value='delete_bill_port'>
          <input type=hidden name=interface_id value='".$port['interface_id']."'>
          <input type=submit value=' Delete ' name='Delete'></form>");
    echo("</td>");
  }
  echo("</table>");
}

echo("<h4>Add Port</h4>");

echo("<form action='' method='post'>
      <input type='hidden' name='action' value='add_bill_port'>
      <input type='hidden' name='bill_id' value='".$bill_id."'>

      <table><tr><td>Device: </td>
       <td><select id='device' class='selector' name='device' onchange='getInterfaceList(this)'>
        <option value=''>Select a device</option>");

$devices = dbFetchRows("SELECT * FROM `devices` ORDER BY hostname");
foreach ($devices as $device)
{
  unset($done);
  foreach ($access_list as $ac) { if ($ac == $device['device_id']) { $done = 1; } }
  if (!$done) { echo("<option value='" . $device['device_id']  . "'>" . $device['hostname'] . "</option>"); }
}

echo("</select></td></tr><tr>
     <td>Interface: </td><td><select class=selector id='interface_id' name='interface_id'>
     </select></td>
     </tr><tr></table><input type='submit' name='Submit' value=' Add '></form>");

?>
