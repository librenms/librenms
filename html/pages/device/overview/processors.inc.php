<?php

$graph_type = "processor_usage";

$processors = dbFetchRows("SELECT * FROM `processors` WHERE device_id = ?", array($device['device_id']));

if (count($processors))
{
  $processor_rows = 0;
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead>");
  echo('<a class="sectionhead" href="device/'.$device['device_id'].'/health/processor/">');
  echo("<img align='absmiddle' src='".$config['base_url']."/images/icons/processor.png'> Processors</a></p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");

  foreach($processors as $proc)
  {
    if (is_integer($processor_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $proc_url   = $config['base_url'] . "/graphs/".$proc['processor_id']."/processor_usage/";

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$proc['processor_descr'];
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=400&amp;height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $mini_url = $config['base_url'] . "/graph.php?id=".$proc['processor_id']."&amp;type=".$graph_type."&amp;from=".$day."&amp;to=".$now."&amp;width=80&amp;height=20&amp;bg=f4f4f4";

    ## REPLACE THIS SHITTY CODE. IT IS ALSO ELSEWHERE.

    $text_descr = rewrite_entity_descr($proc['processor_descr']);

    # disable short hrDeviceDescr. need to make this prettier.
    #$text_descr = short_hrDeviceDescr($proc['processor_descr']);

    $percent = $proc['processor_usage'];

    $background = get_percentage_colours($percent);

    echo("<tr bgcolor=$row_colour><td class=tablehead><a href='".$proc_url."' $proc_popup>" . $text_descr . "</a></td>
           <td width=90><a href='".$proc_url."'  $proc_popup><img src='$mini_url'></a></td>
           <td width=200><a href='".$proc_url."' $proc_popup>
           ".print_percentage_bar (200, 20, $percent, NULL, "ffffff", $background['left'], $percent . "%", "ffffff", $background['right'])."
           </a></td>
         </tr>");
    $processor_rows++;
  }

  echo("</table>");
  echo("</div>");
}

?>
