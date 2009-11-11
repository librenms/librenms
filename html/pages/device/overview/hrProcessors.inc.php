<?php

if(mysql_result(mysql_query("SELECT count(*) from hrDevice WHERE device_id = '" . $device['device_id'] . "' AND hrDeviceType = 'hrDeviceProcessor'"),0)) {

  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Processors</p>");
  echo("<table width=100%>");
  $i = '1';
  $procs = mysql_query("SELECT * FROM `hrDevice` WHERE device_id = '" . $device['device_id'] . "' AND hrDeviceType = 'hrDeviceProcessor'");
  while($proc = mysql_fetch_array($procs)) {

    $proc_url   = "?page=device/".$device['device_id']."/health/hrprocessors/";

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$proc['entPhysicalDescr'];
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['hrDevice_id'] . "&type=hrProcessor&from=$month&to=$now&width=400&height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $text_descr = short_hrDeviceDescr($proc['hrDeviceDescr']);

    if($proc['hrProcessorLoad'] > '60') { $proc_colour='#cc0000'; } else { $proc_colour='#0000cc';  }
    echo("<tr><td class=tablehead width=350><a href='' $proc_popup>" . $text_descr . "</a></td>
           <td width=220><a href='#' $proc_popup>
             <img src='percentage.php?per=" . $proc['hrProcessorLoad'] . "&width=200'></a></td>
           <td style='font-weight: bold; color: $proc_colour'>
           " . $proc['hrProcessorLoad'] . "%</td>
         </tr>");
    $i++;
  }
  echo("</table>");
  echo("</div>");
}

?>
