<?php

$graph_type = "storage_usage";

$drives = dbFetchRows("SELECT * FROM `storage` WHERE device_id = ? ORDER BY `storage_descr` ASC", array($device['device_id']));

if (count($drives))
{
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead>");
  echo('<a class="sectionhead" href="device/'.$device['device_id'].'/health/storage/">');
  echo("<img align='absmiddle' src='".$config['base_url']."/images/icons/storage.png'> Storage</a></p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");
  $drive_rows = '0';

  foreach ($drives as $drive)
  {
    $skipdrive = 0;

    if ($device["os"] == "junos")
    {
      foreach ($config['ignore_junos_os_drives'] as $jdrive)
      {
        if (preg_match($jdrive, $drive["storage_descr"]))
        {
          $skipdrive = 1;
        }
      }
      $drive["storage_descr"] = preg_replace("/.*mounted on: (.*)/", "\\1", $drive["storage_descr"]);
    }

    if ($device['os'] == "freebsd")
    {
      foreach ($config['ignore_bsd_os_drives'] as $jdrive)
      {
        if (preg_match($jdrive, $drive["storage_descr"]))
        {
          $skipdrive = 1;
        }
      }
    }

    if ($skipdrive) { continue; }
    if (is_integer($drive_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
    $perc  = round($drive['storage_perc'], 0);
    $total = formatStorage($drive['storage_size']);
    $free = formatStorage($drive['storage_free']);
    $used = formatStorage($drive['storage_used']);

    $fs_url   = $config['base_url'] . "/graphs/".$drive['storage_id']."/storage_usage/";

    $fs_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$drive['storage_descr'];
    $fs_popup .= "</div><img src=\'graph.php?id=" . $drive['storage_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=400&amp;height=125\'>";
    $fs_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $mini_graph = $config['base_url'] . "/graph.php?id=".$drive['storage_id']."&amp;type=".$graph_type."&amp;from=".$day."&amp;to=".$now."&amp;width=80&amp;height=20&amp;bg=f4f4f4";

    $background = get_percentage_colours($perc);

    echo("<tr bgcolor=$row_colour><td class=tablehead><a href='".$fs_url."' $fs_popup>" . $drive['storage_descr'] . "</a></td>
            <td width=90><a href='".$fs_url."' $fs_popup><img src='$mini_graph' /></a></td>
            <td width=200><a href='".$fs_url."' $fs_popup>".print_percentage_bar (200, 20, $perc, "$used / $total", "ffffff", $background['left'], $perc . "%", "ffffff", $background['right'])."</a></td>
          </tr>");
    $drive_rows++;
  }

  echo("</table>");
  echo("</div>");
}

unset ($drive_rows);

?>
