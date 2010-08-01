
<?php


if(device_permitted($entry['device_id'])) {


$syslog_iter++;
if(!is_integer($syslog_iter/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

  echo("<tr style=\"background-color: $bg_colour\">
    <td width=0></td>");

  echo("<td class=syslog width=140>" . $entry['date'] . "</td>");

  $entry['hostname'] = shorthost($entry['hostname'], 20);

  if($_GET['page'] != "device") { 
    echo("<td width=160><strong>".generatedevicelink($entry)."</strong></td>"); 
  }

  echo("  <td class=syslog>
      <strong>" . $entry['program'] . " : </strong>
      " . htmlspecialchars($entry['msg']) . "
    </td>
    <td></td>
  </tr>");

}

?>
