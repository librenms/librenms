<?php

if (device_permitted($entry['device_id']))
{
  echo("<tr class=\"syslog\">
    <td width=0></td>");

  // Stop shortening hostname. Issue #61
  //$entry['hostname'] = shorthost($entry['hostname'], 20);

  if ($vars['page'] != "device")
  {
    echo("<td class=syslog width=140>" . $entry['date'] . "</td>");
    echo("<td width=160><strong>".generate_device_link($entry)."</strong></td>");
    echo("<td class=syslog><strong>" . $entry['program'] . " : </strong> " . htmlspecialchars($entry['msg']) . "</td>");
  } else {
    echo("<td class=syslog><i>" . $entry['date'] . "</i>&nbsp;&nbsp;&nbsp;<strong>" . $entry['program'] . "</strong>&nbsp;&nbsp;&nbsp;" . htmlspecialchars($entry['msg']) . "</td>");
  }

  echo("</tr>");

}

?>
