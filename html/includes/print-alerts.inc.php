<?php

$hostname = gethostbyid($alert_entry['device_id']);

echo('<tr>
  <td>
    ' . $alert_entry['time_logged'] . '
  </td>');

if (!isset($alert_entry['device'])) {
  $dev = device_by_id_cache($alert_entry['device_id']);
  echo("<td>
    " . generate_device_link($dev, shorthost($dev['hostname'])) . "
  </td>");
}

echo("<td>".$alert_entry['link']."</td>");
echo("<td>".htmlspecialchars($alert_entry['name']) . "</td>

</tr>");

?>
