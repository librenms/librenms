<?php

if (device_permitted($entry['device_id']))
{
  echo("<tr>");

  // Stop shortening hostname. Issue #61
  //$entry['hostname'] = shorthost($entry['hostname'], 20);

  if ($vars['page'] != "device")
  {
    echo("<td>" . $entry['date'] . "</td>");
    echo("<td><strong>".generate_device_link($entry)."</strong></td>");
    echo("<td><strong>" . $entry['program'] . " : </strong> " . htmlspecialchars($entry['msg']) . "</td>");
  } else {
    echo("<td><i>" . $entry['date'] . "</i>&nbsp;&nbsp;&nbsp;<strong>" . $entry['program'] . "</strong>&nbsp;&nbsp;&nbsp;" . htmlspecialchars($entry['msg']) . "</td>");
  }

  echo("</tr>");

}

?>
