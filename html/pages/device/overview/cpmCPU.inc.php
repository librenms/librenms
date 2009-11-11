<?php

if(mysql_result(mysql_query("SELECT count(*) from cpmCPU WHERE device_id = '" . $device['device_id'] . "'"),0)) {
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Processors</p>");
  echo("<table width=100%>");
  $i = '1';
  $procs = mysql_query("SELECT * FROM `cpmCPU` WHERE device_id = '" . $device['device_id'] . "'");
  while($proc = mysql_fetch_array($procs)) {

    $proc_url   = "?page=device/".$device['device_id']."/health/cpm/";
    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$proc['entPhysicalDescr'];
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['cpmCPU_id'] . "&type=cpmCPU&from=$month&to=$now&width=400&height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    if($proc['cpuCPMTotal5minRev'] > '60') { $proc_colour='#cc0000'; } else { $proc_colour='#0000cc';  }
    echo("<tr><td class=tablehead><a href='$proc_url' $proc_popup>" . $proc['entPhysicalDescr'] . "</a></td>
            <td><a href='#' $proc_popup><img src='percentage.php?per=" . $proc['cpmCPUTotal5minRev'] . "'></a></td>
            <td style='font-weight: bold; color: $drv_colour'>" . $proc['cpmCPUTotal5minRev'] . "%</td>
          </tr>");
    $i++;
  }
  echo("</table>");
  echo("</div>");
}

?>
