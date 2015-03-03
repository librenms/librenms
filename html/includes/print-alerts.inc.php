<?php

$hostname = gethostbyid($alert_entry['device_id']);
$alert_state = $alert_entry['state'];

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

echo("<td>".htmlspecialchars($alert_entry['name']) . "</td>");

if ($alert_state!='') {
    if ($alert_state=='0') {
        echo("<td><b><span class='glyphicon glyphicon-ok' style='color:green'></span> Ok</b></td>");
    }
    elseif ($alert_state=='1') {
        echo("<td><b><span class='glyphicon glyphicon-remove' style='color:red'></span> Alert</b></td>");
    }
    elseif ($alert_state=='2') {
        echo("<td><b><span class='glyphicon glyphicon-info-sign' style='color:lightgrey'></span> Ack</b></td>");
    }
    elseif ($alert_state=='3') {
        echo("<td><b><span class='glyphicon glyphicon-arrow-down' style='color:orange'></span> Worse</b></td>");
    }
    elseif ($alert_state=='4') {
        echo("<td><b><span class='glyphicon glyphicon-arrow-up' style='color:khaki'></span> Better</b></td>");
    }
}

echo("</tr>");

?>
