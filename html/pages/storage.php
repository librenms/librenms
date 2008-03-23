<?php

if($_SESSION['userlevel'] >= '5') {
  $sql  = "SELECT * FROM `storage` AS S, `devices` AS D WHERE S.host_id = D.device_id ORDER BY D.hostname, S.hrStorageDescr";
} else {
  $sql  = "SELECT * FROM `storage` AS S, `devices` AS D, devices_perms as P WHERE D.host_id = D.device_id AND ";
  $sql .= "D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, S.hrStorageDescr";
}

$query = mysql_query($sql);

echo("<div style='padding: 5px;'>
        <table width=100% cellspacing=0 cellpadding=2>");

echo("<tr class=tablehead>
        <th width=280>Device</th>
        <th width=175>Storage</th>
        <th width=360>Usage</th>
        <th width=60></th>
        <th width=100>Size</th>
        <th width=100>Used</th>
      </tr>");

$row = 1;

while($drive = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $total = $drive['hrStorageSize'] * $drive['hrStorageAllocationUnits'];
    $used  = $drive['hrStorageUsed'] * $drive['hrStorageAllocationUnits'];
    $perc  = round($drive['storage_perc'], 0);
    $total = formatStorage($total);
    $used = formatStorage($used);

    $store_url    = "graph.php?id=" . $drive['storage_id'] . "&type=unixfs&from=$month&to=$now&width=400&height=125";
    $store_popup = "onmouseover=\"return overlib('<img src=\'$store_url\'>', LEFT);\" onmouseout=\"return nd();\"";

    $drv_colour = percent_colour($perc);

    echo("<tr bgcolor='$row_colour'><th>" . generatedevicelink($drive) . "</td><td class=tablehead>" . $drive['hrStorageDescr'] . "</td><td>
          <a href='#' $store_popup><img src='percentage.php?per=" . $perc . "&width=350'></a>
          </td><td style='font-weight: bold; color: $drv_colour'>" . $perc . "%</td><td>" . $total . "</td><td>" . $used . "</td></tr>");


    $row++;

}

echo("</table></div>");


?>

