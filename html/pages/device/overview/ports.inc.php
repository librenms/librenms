<?php

if ($ports['total'])
{
  echo('<div style="background-color: #eeeeee; margin: 5px; padding: 5px;">');

  $graph_array['height'] = "100";
  $graph_array['width']  = "485";
  $graph_array['to']     = $config['time']['now'];
  $graph_array['device']          = $device['device_id'];
  $graph_array['type']   = "device_bits";
  $graph_array['from']   = $config['time']['day'];
  $graph_array['legend'] = "no";
  $graph = generate_graph_tag($graph_array);

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width']);
  $link = generate_url($link_array);

  $graph_array['width']  = "210";
  $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - Device Traffic");

  echo(overlib_link($link, $graph, $overlib_content, NULL));

  echo('  <div style="height: 5px;"></div>');

  echo('  <table class="tablehead" cellpadding="2" cellspacing="0" width="100%">
    <tr bgcolor="' . $ports_colour . '" align="center"><td></td>
      <td width="25%"><img src="images/16/connect.png" align="absmiddle"> ' . $ports['total'] . '</td>
      <td width="25%" class="green"><img src="images/16/if-connect.png" align="absmiddle"> ' . $ports['up'] . '</td>
      <td width="25%" class="red"><img src="images/16/if-disconnect.png" align="absmiddle"> ' . $ports['down'] . '</td>
      <td width="25%" class="grey"><img src="images/16/if-disable.png" align="absmiddle"> ' . $ports['disabled'] . '</td>
    </tr>
  </table>');

  echo('  <div style="margin: 8px; font-size: 11px; font-weight: bold;">');

  $ifsep = "";

  foreach (dbFetchRows("SELECT * FROM `ports` WHERE device_id = ? AND `deleted` != '1'", array($device['device_id'])) as $data)
  {
    $data = ifNameDescr($data);
    $data = array_merge($data, $device);
    echo("$ifsep" . generate_port_link($data, makeshortif(strtolower($data['label']))));
    $ifsep = ", ";
  }

  unset($ifsep);
  echo("  </div>");
  echo("</div>");
}

?>
