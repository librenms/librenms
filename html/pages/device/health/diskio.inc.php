<?php

$sql = "SELECT * FROM `ucd_diskio` WHERE device_id = '" . $_GET[id] . "' ORDER BY diskio_descr";
$query = mysql_query($sql);

echo("<table cellspacing=0 cellpadding=5 width=100%>");

#echo("<tr class=tablehead>
#        <th width=250>Drive</th>
#        <th width=420>Usage</th>
#        <th width=50>Free</th>
#        <th></th>
#      </tr>");

$row = 1;

while($drive = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

#    $total = $drive['storage_size'];
#    $used  = $drive['storage_used'];
#    $free  = $drive['storage_free'];
#    $perc  = round($drive['storage_perc'], 0);
#    $used = formatStorage($used);
#    $total = formatStorage($total);
#    $free = formatStorage($free);

    $fs_url   = "/device/".$device['device_id']."/health/diskio/";

    $fs_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['diskio_descr'];
    $fs_popup .= "</div><img src=\'graph.php?id=" . $drive['diskio_id'] . "&type=ucd_diskio&from=$month&to=$now&width=400&height=125\'>";
    $fs_popup .= "', RIGHT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\"";

    if($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }

    echo("<tr bgcolor='$row_colour'><th><a href='$fs_url' $fs_popup>" . $drive['diskio_descr'] . "</a></td></tr>");

  $types = array("diskio_bits", "diskio_ops");

  foreach($types as $graph_type) {

    echo('<tr bgcolor="'.$row_colour.'"><td colspan=5>');

    $graph_array['height'] = "100";
    $graph_array['width']  = "215";
    $graph_array['to']     = $now;
    $graph_array['id']     = $drive['diskio_id'];
    $graph_array['type']   = $graph_type;

    $periods = array('day', 'week', 'month', 'year');

    foreach($periods as $period) {
      $graph_array['from']     = $$period;
      $graph_array_zoom   = $graph_array; $graph_array_zoom['height'] = "150"; $graph_array_zoom['width'] = "400";
      echo(overlib_link($_SERVER['REQUEST_URI'], generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
    }

    echo('</tr></td>');

  }

  $row++;

}

  echo("</table>");

?>
