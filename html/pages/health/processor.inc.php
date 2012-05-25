<?php

$graph_type = "processor_usage";

echo("<div style='margin-top: 5px; padding: 0px;'>");
echo("  <table width=100% cellpadding=6 cellspacing=0>");

echo("<tr class=tablehead>
        <th width=280>Device</th>
        <th>Processor</th>
        <th width=100></th>
        <th width=280>Usage</th>
      </tr>");

foreach (dbFetchRows("SELECT * FROM `processors` AS P, `devices` AS D WHERE D.device_id = P.device_id ORDER BY D.hostname") as $proc)
{
  if (device_permitted($proc['device_id']))
  {
    $device = $proc;

    /// FIXME should that really be done here? :-)
    $text_descr = $proc['processor_descr'];
    $text_descr = str_replace("Routing Processor", "RP", $text_descr);
    $text_descr = str_replace("Switching Processor", "SP", $text_descr);
    $text_descr = str_replace("Sub-Module", "Module ", $text_descr);
    $text_descr = str_replace("DFC Card", "DFC", $text_descr);

    $proc_url   = "device/".$device['device_id']."/health/processor/";

    $mini_url = "graph.php?id=".$proc['processor_id']."&amp;type=".$graph_type."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=80&amp;height=20&amp;bg=f4f4f400";

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$text_descr;
    $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['month']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $perc = round($proc['processor_usage']);

    $background = get_percentage_colours($perc);

    echo("    <tr class=\"health\">
               <td>".generate_device_link($proc)."</td>
               <td class=\"tablehead\"><a href='".$proc_url."' $proc_popup>" . $text_descr . "</a></td>
               <td width=\"90\"><a href=\"".$proc_url."\"  $proc_popup><img src=\"$mini_url\" /></a></td>
               <td width=\"200\"><a href=\"".$proc_url."\" $proc_popup>
           ".print_percentage_bar (400, 20, $perc, $perc."%", "ffffff", $background['left'], (100 - $perc)."%" , "ffffff", $background['right']));
    echo('</a></td>
             </tr>');

    if ($vars['view'] == "graphs")
    {
      echo('    <tr></tr><tr class="health"><td colspan="5">');

      $daily_graph   = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
      $daily_url     = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

      $weekly_graph  = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['week']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
      $weekly_url    = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['week']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

      $monthly_graph = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
      $monthly_url   = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

      $yearly_graph  = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['yearh']."&amp;to=".$config['time']['now']."&amp;width=211&amp;height=100";
      $yearly_url    = "graph.php?id=" . $proc['processor_id'] . "&amp;type=".$graph_type."&amp;from=".$config['time']['yearh']."&amp;to=".$config['time']['now']."&amp;width=400&amp;height=150";

      echo("      <a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src=\"$daily_graph\" border=\"0\"></a> ");
      echo("      <a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src=\"$weekly_graph\" border=\"0\"></a> ");
      echo("      <a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src=\"$monthly_graph\" border=\"0\"></a> ");
      echo("      <a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src=\"$yearly_graph\" border=\"0\"></a>");
      echo("  </td>
  </tr>");

    } #end graphs if
  }
}

echo("</table>");
echo("</div>");

?>
