<?php

if(mysql_result(mysql_query("SELECT count(*) from processors WHERE device_id = '" . $device['device_id'] . "'"),0)) {
  $processor_rows = 0;
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead><img align='absmiddle' src='".$config['base_url']."/images/icons/overview/processors.png'> Processors</p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");
  $i = '1';
  $procs = mysql_query("SELECT * FROM `processors` WHERE device_id = '" . $device['device_id'] . "' ORDER BY processor_descr ASC");
  while($proc = mysql_fetch_array($procs)) {
    if(is_integer($processor_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $proc_url   = $config['base_url'] . "/device/".$device['device_id']."/health/processors/";

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$proc['processor_descr'];
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['processor_id'] . "&type=processor&from=$month&to=$now&width=400&height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $mini_url = $config['base_url'] . "/graph.php?id=".$proc['processor_id']."&type=processor&from=".$day."&to=".$now."&width=80&height=20&bg=f4f4f4";

    ## REPLACE THIS SHITTY CODE. IT IS ALSO ELSEWHERE.

    $text_descr = $proc['processor_descr'];
    $text_descr = str_replace("Routing Processor", "RP", $text_descr);
    $text_descr = str_replace("Switching Processor", "SP", $text_descr);
    $text_descr = str_replace("Sub-Module", "Module ", $text_descr);
    $text_descr = str_replace("DFC Card", "DFC", $text_descr);

    # disable short hrDeviceDescr. need to make this prettier.
    #$text_descr = short_hrDeviceDescr($proc['processor_descr']);

    $perc = $proc['processor_usage'];

    if($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }


    echo("<tr bgcolor=$row_colour><td class=tablehead><a href='".$proc_url."' $proc_popup>" . $text_descr . "</a></td>
           <td width=90><a href='".$proc_url."'  $proc_popup><img src='$mini_url'></a></td>
           <td width=200><a href='".$proc_url."' $proc_popup>
           ".print_percentage_bar (200, 20, $perc, NULL, "ffffff", $left_background, $perc . "%", "ffffff", $right_background)."
           </a></td>
         </tr>");
    $processor_rows++;    
  }
  echo("</table>");
  echo("</div>");
}

?>
