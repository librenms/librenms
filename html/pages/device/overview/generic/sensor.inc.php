<?php

$sensors = dbFetchRows("SELECT * FROM `sensors` WHERE `sensor_class` = ? AND device_id = ? ORDER BY `poller_type`, `sensor_index`", array($sensor_class, $device['device_id']));

if (count($sensors))
{
  echo('<div style="background-color: #eeeeee; margin: 5px; padding: 5px;">');
  echo('<p style="padding: 0px 5px 5px;" class="sectionhead"><a class="sectionhead" href="device/device='.$device['device_id'].'/tab=health/metric=' . strtolower($sensor_type) . '/"><img align="absmiddle" src="images/icons/' . strtolower($sensor_type) . '.png"> ' . $sensor_type . '</a></p>');
  echo('<table width="100%" valign="top">');
  foreach ($sensors as $sensor)
  {
    if ($config['memcached']['enable'])
    {
      $sensor['sensor_current'] = $memcache->get('sensor-'.$sensor['sensor_id'].'-value');
    }

    if (empty($sensor['sensor_current']))
    {
      $sensor['sensor_current'] = "NaN";
    }

    ### FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
    ### FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
    ### FIXME - DUPLICATED IN health/sensors

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
    $link = generate_url($link_array);

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

    echo("<tr class=device-overview>
           <td class=tablehead style='padding-left:5px;'><strong>".overlib_link($link, $sensor['sensor_descr'], $overlib_content)."</strong></td>
           <td width=80 align=right class=tablehead>".overlib_link($link, $sensor_minigraph, $overlib_content)."</td>
           <td width=80 align=right class=tablehead>".overlib_link($link, "<span " . ($sensor['sensor_current'] < $sensor['sensor_limit_low'] || $sensor['sensor_current'] > $sensor['sensor_limit'] ? "style='color: red'" : '') . '>' . $sensor['sensor_current'] . $sensor_unit . "</span>", $overlib_content)."</td>
          </tr>");
  }

  echo("</table>");
  echo("</div>");
}

?>
