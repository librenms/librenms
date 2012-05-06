<?php

$graph_type = "toner_usage";

$toners = dbFetchRows("SELECT * FROM `toner` WHERE device_id = ?", array($device['device_id']));

if (count($toners))
{
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead>");
  echo('<a class="sectionhead" href="device/device='.$device['device_id'].'/tab=toner/">');
  echo("<img align='absmiddle' src='images/icons/toner.png'> Toner</a></p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");

  foreach ($toners as $toner)
  {
    $percent  = round($toner['toner_current'], 0);
    $total = formatStorage($toner['toner_size']);
    $free = formatStorage($toner['toner_free']);
    $used = formatStorage($toner['toner_used']);

    $background = toner2colour($toner['toner_descr'], $percent);

    $graph_array           = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "210";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $toner['toner_id'];
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link = generate_url($link_array);

    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - " . $toner['toner_descr']);

    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.

    $minigraph =  generate_graph_tag($graph_array);

    echo("<tr class=device-overview>
               <td class=tablehead>".overlib_link($link, $toner['toner_descr'], $overlib_content)."</td>
           <td width=90>".overlib_link($link, $minigraph, $overlib_content)."</td>
           <td width=200>".overlib_link($link, print_percentage_bar (200, 20, $percent, NULL, "ffffff", $background['left'], $percent . "%", "ffffff", $background['right']), $overlib_content)."
           </a></td>
         </tr>");
  }

  echo("</table>");
  echo("</div>");
}

unset ($toner_rows);

?>
