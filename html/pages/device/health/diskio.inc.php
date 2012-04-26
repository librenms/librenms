<?php

echo('<table cellspacing="0" cellpadding="5" width="100%">');

#echo("<tr class=tablehead>
#        <th width=250>Drive</th>
#        <th width=420>Usage</th>
#        <th width=50>Free</th>
#        <th></th>
#      </tr>");

$row = 1;

foreach (dbFetchRows("SELECT * FROM `ucd_diskio` WHERE device_id = ? ORDER BY diskio_descr", array($device['device_id'])) as $drive)
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $fs_url   = "device/device=".$device['device_id']."/tab=health/metric=diskio/";

  $graph_array_zoom['id']     = $drive['diskio_id'];
  $graph_array_zoom['type']   = "diskio_ops";
  $graph_array_zoom['width']  = "400";
  $graph_array_zoom['height'] = "125";
  $graph_array_zoom['from']   = $config['time']['twoday'];
  $graph_array_zoom['to']     = $config['time']['now'];

  echo("<tr bgcolor='$row_colour'><th>");
  echo(overlib_link($fs_url, $drive['diskio_descr'], generate_graph_tag($graph_array_zoom),  NULL));
  echo("</th></tr>");

  $types = array("diskio_bits", "diskio_ops");

  foreach ($types as $graph_type)
  {
    echo('<tr bgcolor="'.$row_colour.'"><td colspan=5>');

    $graph_array           = array();
    $graph_array['id']     = $drive['diskio_id'];
    $graph_array['type']   = $graph_type;

    include("includes/print-graphrow.inc.php");

    echo("</td></tr>");
  }

  $row++;
}

echo("</table>");

?>
