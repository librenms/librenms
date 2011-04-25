<?php

$sql = "SELECT * FROM `ucd_diskio` WHERE device_id = '" . $device['device_id'] . "' ORDER BY diskio_descr";
$query = mysql_query($sql);

echo('<table cellspacing="0" cellpadding="5" width="100%">');

#echo("<tr class=tablehead>
#        <th width=250>Drive</th>
#        <th width=420>Usage</th>
#        <th width=50>Free</th>
#        <th></th>
#      </tr>");

$row = 1;

while ($drive = mysql_fetch_assoc($query))
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $fs_url   = "device/".$device['device_id']."/health/diskio/";

  $fs_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['diskio_descr'];
  $fs_popup .= "</div><img src=\'graph.php?id=" . $drive['diskio_id'] . "&amp;type=diskio_ops&amp;from=$month&amp;to=$now&amp;width=400&amp;height=125\'>";
  $fs_popup .= "', RIGHT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\"";

  echo("<tr bgcolor='$row_colour'><th><a href='$fs_url' $fs_popup>" . $drive['diskio_descr'] . "</a></td></tr>");

  $types = array("diskio_bits", "diskio_ops");

  foreach ($types as $graph_type)
  {
    echo('<tr bgcolor="'.$row_colour.'"><td colspan=5>');

    $graph_array['id']     = $drive['diskio_id'];
    $graph_array['type']   = $graph_type;

    include("includes/print-quadgraphs.inc.php");

    echo("</td></tr>");
  }

  $row++;
}

echo("</table>");

?>
