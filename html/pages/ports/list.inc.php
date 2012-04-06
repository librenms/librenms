
<table cellpadding="3" cellspacing="0" class="devicetable sortable" width="100%">

<?php

echo('<tr class="tablehead"><td></td>');

$cols = array('device' => 'Device',
              'port' => 'Port',
              'speed' => 'Speed',
              'traffic_in' => 'Down',
              'traffic_out' => 'Up',
              'media' => 'Media',
              'descr' => 'Description' );

foreach ($cols as $sort => $col)
{
  if ($vars['sort'] == $sort)
  {
    echo('<th>'.$col.' *</th>');
  } else {
    echo('<th><a href="'. generate_url($vars, array('sort' => $sort)).'">'.$col.'</a></th>');
  }
}

echo("      </tr>");

$row = 1;

$ports_disabled = 0; $ports_down = 0; $ports_up = 0; $ports_total = 0;

foreach ($ports as $port)
{

  if ($port['ifAdminStatus'] == "down") { $ports_disabled++;
  } elseif ($port['ifAdminStatus'] == "up" && $port['ifOperStatus']== "down") { $ports_down++;
  } elseif ($port['ifAdminStatus'] == "up" && $port['ifOperStatus']== "up") { $ports_up++; }
  $ports_total++;

  if (port_permitted($port['interface_id'], $port['device_id']))
  {
    if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $speed = humanspeed($port['ifSpeed']);
    $type = humanmedia($port['ifType']);
    $ifclass = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);

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
          <td width=150 class=list-bold><a class='".$ifclass."'href='" . generate_port_url($port) . "'>".fixIfName($port['label'])." $error_img</td>
          <td width=110 >$speed</td>
          <td width=100 class=green>".$port['in_rate']."</td>
          <td width=100 class=blue>".$port['out_rate']."</td>
          <td width=150>$type</td>
          <td>" . $port['ifAlias'] . "</td>
        </tr>\n");

    $row++;
  }
}

echo('<tr><td colspan="7">');
echo("<strong>Matched Ports: $ports_total ( <span class=green>Up $ports_up</span> | <span class=red>Down $ports_down</span> | Disabled $ports_disabled )</strong>");
echo('</td></tr></table>');

?>
