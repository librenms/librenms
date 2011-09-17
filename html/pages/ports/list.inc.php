
<table cellpadding=3 cellspacing=0 class="devicetable sortable" width=100%>

<?php

echo("<tr class=tablehead><td></td><th>Device</a></th><th>Interface</th><th>Speed</th><th>Down</th><th>Up</th><th>Media</th><th>Description</th></tr>");

$row = 1;

foreach (dbFetchRows($query, $param) as $interface)
{

  if (port_permitted($interface['interface_id'], $interface['device_id']))
  {

    if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $speed = humanspeed($interface['ifSpeed']);
    $type = humanmedia($interface['ifType']);

    if ($interface['in_errors'] > 0 || $interface['out_errors'] > 0)
    {
      $error_img = generate_port_link($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
    } else { $error_img = ""; }

    $interface['in_rate'] = formatRates($interface['ifInOctets_rate'] * 8);
    $interface['out_rate'] = formatRates($interface['ifOutOctets_rate'] * 8);

    $interface = ifLabel($interface, $device);
    echo("<tr bgcolor=$row_colour>
          <td width=5></td>
          <td width=200 class=list-bold><a href='" . generate_device_url($interface) . "'>".$interface['hostname']."</a></td>
          <td width=150 class=list-bold><a href='" . generate_port_url($interface) . "'>".fixIfName($interface['label'])." $error_img</td>
          <td width=110 >$speed</td>
          <td width=110 class=green>".$interface['in_rate']."</td>
          <td width=110 class=blue>".$interface['out_rate']."</td>
          <td width=200>$type</td>
          <td>" . $interface['ifAlias'] . "</td>
        </tr>\n");

    $row++;
  }
}

echo("</table>");

?>
