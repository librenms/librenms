<?php

$graph_type = "processor_usage";

$processors = dbFetchRows("SELECT * FROM `processors` WHERE device_id = ?", array($device['device_id']));

if (count($processors))
{
  $processor_rows = 0;
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead>");
  echo('<a class="sectionhead" href="device/device='.$device['device_id'].'/tab=health/metric=processor/">');
  echo("<img align='absmiddle' src='images/icons/processor.png'> Processors</a></p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");

  foreach ($processors as $proc)
  {
    if (is_integer($processor_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
    ## REPLACE THIS SHITTY CODE. IT IS ALSO ELSEWHERE.

    $text_descr = rewrite_entity_descr($proc['processor_descr']);

    # disable short hrDeviceDescr. need to make this prettier.
    #$text_descr = short_hrDeviceDescr($proc['processor_descr']);
    $percent = $proc['processor_usage'];
    $background = get_percentage_colours($percent);
    $graph_colour = str_replace("#", "", $row_colour);

    $graph_array           = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "210";
    $graph_array['to']     = $now;
    $graph_array['id']     = $proc['processor_id'];
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $day;
    $graph_array['legend'] = "no";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link = generate_url($link_array);

    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - " . $text_descr);

    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = $graph_colour;

    $minigraph =  generate_graph_tag($graph_array);

    echo("<tr bgcolor=$row_colour>
           <td class=tablehead>".overlib_link($link, $text_descr, $overlib_content)."</td>
           <td width=90>".overlib_link($link, $minigraph, $overlib_content)."</td>
           <td width=200>".overlib_link($link, print_percentage_bar (200, 20, $percent, NULL, "ffffff", $background['left'], $percent . "%", "ffffff", $background['right']), $overlib_content)."
           </a></td>
         </tr>");
    $processor_rows++;
  }

  echo("</table>");
  echo("</div>");
}

?>
