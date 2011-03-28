<?php

$graph_type = "storage_usage";

$sql = "SELECT * FROM `storage` WHERE device_id = '" . $device['device_id'] . "' ORDER BY storage_descr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

echo("<tr class=tablehead>
        <th width=250>Drive</th>
        <th width=420>Usage</th>
        <th width=50>Free</th>
        <th></th>
      </tr>");

$row = 1;

while ($drive = mysql_fetch_array($query))
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $total = $drive['storage_size'];
  $used  = $drive['storage_used'];
  $free  = $drive['storage_free'];
  $perc  = round($drive['storage_perc'], 0);
  $used = formatStorage($used);
  $total = formatStorage($total);
  $free = formatStorage($free);

  $fs_url   = "device/".$device['device_id']."/health/storage/";

  $fs_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['storage_descr'];
  $fs_popup .= "</div><img src=\'graph.php?id=" . $drive['storage_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=400&amp;height=125\'>";
  $fs_popup .= "', RIGHT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\"";

  if ($perc > '90') { $left_background='c4323f'; $right_background='C96A73'; }
  elseif ($perc > '75') { $left_background='bf5d5b'; $right_background='d39392'; }
  elseif ($perc > '50') { $left_background='bf875b'; $right_background='d3ae92'; }
  elseif ($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3'; }
  else { $left_background='9abf5b'; $right_background='bbd392'; }

  echo("<tr bgcolor='$row_colour'><th><a href='$fs_url' $fs_popup>" . $drive['storage_descr'] . "</a></td><td>
          <a href='$fs_url' $fs_popup>".print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $left_background, $perc . "%", "ffffff", $right_background)."</a>
          </td><td>" . $free . "</td><td></td></tr>");

  $graph_array['id'] = $drive['storage_id'];
  $graph_array['type'] = $graph_type;

  echo("<tr bgcolor='$row_colour'><td colspan=6>");

  include("includes/print-quadgraphs.inc.php");

  echo("</td></tr>");

  $row++;
}

echo("</table>");

?>