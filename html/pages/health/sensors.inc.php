<?php

// FIXME - a little ugly...

if ($_SESSION['userlevel'] >= '5')
{
  $sql = "SELECT * FROM `sensors` AS S, `devices` AS D WHERE S.sensor_class='".$class."' AND S.device_id = D.device_id ORDER BY D.hostname, S.sensor_descr";
  $param = array();
} else {
  $sql = "SELECT * FROM `sensors` AS S, `devices` AS D, devices_perms as P WHERE S.sensor_class='".$class."' AND S.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = ? ORDER BY D.hostname, S.sensor_descr";
  $param = array($_SESSION['user_id']);
}

echo('<table cellspacing="0" cellpadding="6" width="100%">');

echo('<tr class=tablehead>
        <th width="280">Device</th>
        <th width="180">Sensor</th>
        <th></th>
        <th></th>
        <th width="100">Current</th>
        <th width="250">Range limit</th>
        <th>Notes</th>
      </tr>');

foreach (dbFetchRows($sql, $param) as $sensor)
{

  if ($config['memcached']['enable'])
  {
    $sensor['sensor_current'] = $memcache->get('sensor-'.$sensor['sensor_id'].'-value');
    if($debug) { echo("Memcached[".'sensor-'.$sensor['sensor_id'].'-value'."=".$sensor['sensor_current']."]"); }
  }

  if (empty($sensor['sensor_current']))
  {
    $sensor['sensor_current'] = "NaN";
  } else {
    if ($sensor['sensor_current'] >= $sensor['sensor_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }
  }

    // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
    // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
    // FIXME - DUPLICATED IN device/overview/sensors

    $graph_colour = str_replace("#", "", $row_colour);

    $graph_array           = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "210";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $sensor['sensor_id'];
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link_graph = generate_url($link_array);

    $link = generate_url(array("page" => "device", "device" => $sensor['device_id'], "tab" => "health", "metric" => $sensor['sensor_class']));

    $overlib_content = '<div style="width: 580px;"><h2>'.$device['hostname']." - ".$sensor['sensor_descr']."</h1>";
    foreach (array('day','week','month','year') as $period)
    {
      $graph_array['from']        = $config['time'][$period];
      $overlib_content .= str_replace('"', "\'", generate_graph_tag($graph_array));
    }
    $overlib_content .= "</div>";

    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
    $graph_array['from'] = $config['time']['day'];
    $sensor_minigraph =  generate_graph_tag($graph_array);

    $sensor['sensor_descr'] = truncate($sensor['sensor_descr'], 48, '');

    echo('<tr class="health">
          <td class=list-bold>' . generate_device_link($sensor) . '</td>
          <td>'.overlib_link($link, $sensor['sensor_descr'],$overlib_content).'</td>
          <td width=100>'.overlib_link($link_graph, $sensor_minigraph, $overlib_content).'</td>
          <td width=50>'.$alert.'</td>
          <td style="text-align: center; font-weight: bold;">' . $sensor['sensor_current'] . $unit . '</td>
          <td style="text-align: center">' . round($sensor['sensor_limit_low'],2) . $unit . ' - ' . round($sensor['sensor_limit'],2) . $unit . '</td>
          <td>' . (isset($sensor['sensor_notes']) ? $sensor['sensor_notes'] : '') . '</td>
        </tr>
     ');

  if ($vars['view'] == "graphs")
  {
    echo("<tr></tr><tr class='health'><td colspan=7>");

    $daily_graph   = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
    $daily_url     = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

    $weekly_graph  = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['week']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
    $weekly_url    = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['week']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

    $monthly_graph = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
    $monthly_url   = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

    $yearly_graph  = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['yearh']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
    $yearly_url    = "graph.php?id=" . $sensor['sensor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['yearh']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

    echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_graph' border=0></a> ");
    echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_graph' border=0></a> ");
    echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_graph' border=0></a> ");
    echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_graph' border=0></a>");
    echo("</td></tr>");
  } # endif graphs
}

echo("</table>");

?>
