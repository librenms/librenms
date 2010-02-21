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
        <th>Storage</th>
        <th width=420>Usage</th>
        <th width=100>Free</th>
      </tr>");

$row = 1;

while($drive = mysql_fetch_array($query)) {

    $skipdrive = 0;

    if ($drive["os"] == "junos") {
        foreach ($config['ignore_junos_os_drives'] as $jdrive) {
            if (preg_match($jdrive, $drive["hrStorageDescr"])) {
                $skipdrive = 1;
            }
        }
    }

    if ($skipdrive) { continue; }
    if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $total = $drive['hrStorageSize'] * $drive['hrStorageAllocationUnits'];
    $used  = $drive['hrStorageUsed'] * $drive['hrStorageAllocationUnits'];
    $free  = $total - $used;
    $perc  = round($drive['storage_perc'], 0);
    $total = formatStorage($total);
    $used = formatStorage($used);

    $store_url    = "graph.php?id=" . $drive['storage_id'] . "&type=hrstorage&from=$month&to=$now&width=400&height=125";
    $store_popup = "onmouseover=\"return overlib('<img src=\'$store_url\'>', LEFT);\" onmouseout=\"return nd();\"";

    if($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }


    echo("<tr bgcolor='$row_colour'><td>" . generatedevicelink($drive) . "</td><td class=tablehead>" . $drive['hrStorageDescr'] . "</td><td>
          <a href='#' $store_popup>".print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $left_background, $perc . "%", "ffffff", $right_background)."</a>
          </td><td>" . formatStorage($free) . "</td></tr>");


    $row++;

}

echo("</table></div>");


?>

