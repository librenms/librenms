<?php

$sql = "SELECT * FROM `storage` WHERE host_id = '" . $_GET[id] . "' ORDER BY hrStorageDescr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

echo("<tr class=tablehead>
        <th width=200>Drive</th>
        <th width=360>Usage</th>
        <th width=50>Used</th>
        <th width=50>Total</th>
        <th width=50>Free</th>
        <th></th>
      </tr>");

$row = 1;

while($drive = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $total = $drive['hrStorageSize'] * $drive['hrStorageAllocationUnits'];
    $used  = $drive['hrStorageUsed'] * $drive['hrStorageAllocationUnits'];
    $perc  = round($drive['storage_perc'], 0);
    $total = formatStorage($total);
    $used = formatStorage($used);

    $fs_url   = "?page=device&id=".$device['device_id']."&section=dev-storage";

    $fs_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['hrStorageDescr'];
    $fs_popup .= "</div><img src=\'graph.php?id=" . $drive['storage_id'] . "&type=unixfs&from=$month&to=$now&width=400&height=125\'>";
    $fs_popup .= "', RIGHT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\"";

    $drv_colour = percent_colour($perc);

    echo("<tr bgcolor='$row_colour'><th><a href='$fs_url' $fs_popup>" . $drive['hrStorageDescr'] . "</a></td><td>
          <a href='$fs_url' $fs_popup><img src='percentage.php?per=" . $perc . "&width=350'></a>
          </td><td style='font-weight: bold; color: $drv_colour'>" . $perc . "%</td><td>" . $total . "</td><td>" . $used . "</td><td></td></tr>");


  $graph_type = "unixfs";

// start temperature graphs

  $daily_temp   = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$day&to=$now&width=212&height=100";
  $daily_url       = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$day&to=$now&width=400&height=150";

  $weekly_temp  = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$week&to=$now&width=212&height=100";
  $weekly_url      = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$week&to=$now&width=400&height=150";

  $monthly_temp = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$month&to=$now&width=212&height=100";
  $monthly_url     = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$month&to=$now&width=400&height=150";

  $yearly_temp  = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$year&to=$now&width=212&height=100";
  $yearly_url  = "graph.php?id=" . $drive['storage_id'] . "&type=$graph_type&from=$year&to=$now&width=400&height=150";

  echo("<tr bgcolor='$row_colour'><td colspan=6>");

  echo("<a onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['hrStorageDescr']."</div><img src=\'$daily_url\'>', LEFT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\">
        <img src='$daily_temp' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['hrStorageDescr']."</div><img src=\'$weekly_url\'>', LEFT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\">
        <img src='$weekly_temp' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['hrStorageDescr']."</div><img src=\'$monthly_url\'>', LEFT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\">
        <img src='$monthly_temp' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['hrStorageDescr']."</div><img src=\'$yearly_url\'>', LEFT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\">
        <img src='$yearly_temp' border=0></a>");

  echo("</td></tr>");


  $row++;

}

echo("</table>");


?>
