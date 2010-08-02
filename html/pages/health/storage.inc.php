<?php

$graph_type = "storage_usage";

$sql  = "SELECT * FROM `storage` AS S, `devices` AS D WHERE S.device_id = D.device_id ORDER BY D.hostname, S.storage_descr";

$query = mysql_query($sql);

echo("<div style='padding: 5px;'>
        <table width=100% cellspacing=0 cellpadding=6>");

echo("<tr class=tablehead>
        <th width=280>Device</th>
        <th>Storage</th>
        <th width=100></th>
        <th width=280>Usage</th>
        <th width=50>Used</th>
      </tr>");

$row = 1;

while($drive = mysql_fetch_array($query)) {

  if(device_permitted($drive['device_id'])){

    $skipdrive = 0;

    if ($drive["os"] == "junos") {
        foreach ($config['ignore_junos_os_drives'] as $jdrive) {
            if (preg_match($jdrive, $drive["storage_descr"])) {
                $skipdrive = 1;
            }
        }
        $drive["storage_descr"] = preg_replace("/.*mounted on: (.*)/", "\\1", $drive["storage_descr"]);
    }

    if ($drive['os'] == "freebsd") {
        foreach ($config['ignore_bsd_os_drives'] as $jdrive) {
            if (preg_match($jdrive, $drive["storage_descr"])) {
                $skipdrive = 1;
            }
        }
    }

    if ($skipdrive) { continue; }
    if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $perc  = round($drive['storage_perc'], 0);
    $total = formatStorage($drive['storage_size']);
    $free = formatStorage($drive['storage_free']);
    $used = formatStorage($drive['storage_used']);

    $store_url    = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=125";
    $store_popup = "onmouseover=\"return overlib('<img src=\'$store_url\'>', LEFT);\" onmouseout=\"return nd();\"";

    $mini_graph = $config['base_url'] . "/graph.php?id=".$drive['storage_id']."&type=".$graph_type."&from=".$day."&to=".$now."&width=80&height=20&bg=f4f4f4";

    if($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }


    echo("<tr bgcolor='$row_colour'><td>" . generate_device_link($drive) . "</td><td class=tablehead>" . $drive['storage_descr'] . "</td>
         <td><img src='$mini_graph'></td>
         <td>
          <a href='#' $store_popup>".print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $left_background, $free, "ffffff", $right_background)."</a>
          </td><td>$perc"."%</td></tr>");


      if($_GET['optb'] == "graphs") { ## If graphs

  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  $daily_graph   = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$day&to=$now&width=211&height=100";
  $daily_url       = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$day&to=$now&width=400&height=150";

  $weekly_graph  = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$week&to=$now&width=211&height=100";
  $weekly_url      = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$week&to=$now&width=400&height=150";

  $monthly_graph = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$month&to=$now&width=211&height=100";
  $monthly_url     = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=150";

  $yearly_graph  = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$year&to=$now&width=211&height=100";
  $yearly_url  = "graph.php?id=" . $drive['storage_id'] . "&type=".$graph_type."&from=$year&to=$now&width=400&height=150";

  echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_graph' border=0></a>");
  echo("</td></tr>");

    } # endif graphs



    $row++;

  }

}

echo("</table></div>");


?>

