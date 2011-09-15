<?
foreach (dbFetchRows($query, $param) as $interface)
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($interface['ifSpeed']);
  $type = humanmedia($interface['ifType']);

  $interface['in_rate'] = formatRates($interface['ifInOctets_rate'] * 8);
  $interface['out_rate'] = formatRates($interface['ifOutOctets_rate'] * 8);


  if ($interface['in_errors'] > 0 || $interface['out_errors'] > 0)
  {
    $error_img = generate_port_link($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
  } else { $error_img = ""; }

  if (port_permitted($interface['interface_id'], $interface['device_id']))
  {
    $interface = ifLabel($interface, $device);

    $graph_type = "port_" . $subformat;

    echo("<div style='display: block; padding: 1px; margin: 2px; min-width: 393px; max-width:393px; min-height:180px; max-height:180px; text-align: center; float: left; background-color: #f5f5f5;'>
    <a href='device/".$interface['device_id']."/port/".$interface['interface_id']."/' onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$interface['ifDescr']."</div>\
    <img src=\'graph.php?type=$graph_type&amp;id=".$interface['interface_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=450&amp;height=150&amp;title=yes\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=$graph_type&amp;id=".$interface['interface_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=315&amp;height=110&amp;legend=no&amp;title=yes'>
    </a>
    </div>");
  }
}
?>
