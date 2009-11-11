<?php

if(mysql_result(mysql_query("SELECT count(storage_id) from storage WHERE host_id = '" . $device['device_id'] . "'"),0)) {
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Storage</p>");
  echo("<table width=100%>");
  $i = '1';

  echo("<tr class=tablehead><td>Mountpoint</td><td width=203>Usage</td><td width=40></td><td width=75>Total</td>
          <td width=75>Used</td></tr>");
  $drives = mysql_query("SELECT * FROM `storage` WHERE host_id = '" . $device['device_id'] . "'");
  while($drive = mysql_fetch_array($drives)) {
    $total = $drive['hrStorageSize'] * $drive['hrStorageAllocationUnits'];
    $used  = $drive['hrStorageUsed'] * $drive['hrStorageAllocationUnits'];
    $drive['perc']  = round($drive['storage_perc'], 0);
    $total = formatStorage($total);
    $used = formatStorage($used);

    $fs_url   = "/device/".$device['device_id']."/health/storage/";

    $fs_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['hrStorageDescr'];
    $fs_popup .= "</div><img src=\'graph.php?id=" . $drive['storage_id'] . "&type=unixfs&from=$month&to=$now&width=400&height=125\'>";
    $fs_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    if($perc > '80') { $drv_colour='#cc0000'; } else { $drvclass='#0000cc';  }
    echo("<tr><td class=tablehead><a href='".$fs_url."' $fs_popup>" . $drive['hrStorageDescr'] . "</a></td>
            <td><a href='$fs_url' $fs_popup><img src='percentage.php?per=" . $drive['perc'] . "'></a></td>
            <td style='font-weight: bold; color: $drv_colour'>" . $drive['perc'] . "%</td>
            <td>" . $total . "</td>
            <td>" . $used . "</td>
          </tr>");
    $i++;
  }
  echo("</table>");
  echo("</div>");
}


?>
