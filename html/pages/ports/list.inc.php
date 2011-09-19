
<table cellpadding=3 cellspacing=0 class="devicetable sortable" width=100%>

<?php

echo("<tr class=tablehead><td></td><th>Device</a></th><th>Interface</th><th>Speed</th><th>Down</th><th>Up</th><th>Media</th><th>Description</th></tr>");

$row = 1;

foreach ($ports as $port)
{

  if (port_permitted($port['interface_id'], $port['device_id']))
  {

    if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $speed = humanspeed($port['ifSpeed']);
    $type = humanmedia($port['ifType']);

    if ($port['in_errors'] > 0 || $port['out_errors'] > 0)
    {
      $error_img = generate_port_link($port,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
    } else { $error_img = ""; }

    $port['in_rate'] = formatRates($port['ifInOctets_rate'] * 8);
    $port['out_rate'] = formatRates($port['ifOutOctets_rate'] * 8);

    $port = ifLabel($port, $device);
    echo("<tr bgcolor=$row_colour>
          <td width=5></td>
          <td width=200 class=list-bold><a href='" . generate_device_url($port) . "'>".$port['hostname']."</a></td>
          <td width=150 class=list-bold><a href='" . generate_port_url($port) . "'>".fixIfName($port['label'])." $error_img</td>
          <td width=110 >$speed</td>
          <td width=110 class=green>".$port['in_rate']."</td>
          <td width=110 class=blue>".$port['out_rate']."</td>
          <td width=200>$type</td>
          <td>" . $port['ifAlias'] . "</td>
        </tr>\n");

    $row++;
  }
}

echo("</table>");

?>
