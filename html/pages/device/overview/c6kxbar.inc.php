<?php

  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead>");
  echo('<a class="sectionhead" href="device/device='.$device['device_id'].'/tab=health/metric=mempool/">');
  echo("<img align='absmiddle' src='images/16/arrow_switch.png'> Catalyst 6k Crossbar</a></p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");
  $c6kxbar_rows = '0';

foreach($entity_state['group']['c6kxbar'] as $index => $entry)
{
  if (is_integer($c6kxbar_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $entity = dbFetchRow("SELECT * FROM entPhysical WHERE device_id = ? AND entPhysicalIndex = ?", array($device['device_id'], $index));

  echo("<tr bgcolor=$row_colour>
        <td colspan=2 width=200><strong>".$entity['entPhysicalName']."</strong></td>
        <td colspan=4>".$entry['']['cc6kxbarModuleModeSwitchingMode']."</td>
        </tr>");

  foreach($entity_state['group']['c6kxbar'][$index] as $subindex => $fabric)
  {
    if(is_numeric($subindex)) {

    if($fabric['cc6kxbarModuleChannelFabStatus'] == "ok")
    {
      $fabric['mode_class'] = "green";
    } else {
      $fabric['mode_class'] = "red";
    }

    $percent_in = $fabric['cc6kxbarStatisticsInUtil'];
    $background_in = get_percentage_colours($percent_in);

    $percent_out = $fabric['cc6kxbarStatisticsOutUtil'];
    $background_out = get_percentage_colours($percent_out);


    echo("<tr bgcolor=$row_colour>
          <td width=10></td>
          <td width=200><strong>Fabric ".$subindex."</strong></td>
          <td><span style='font-weight: bold;' class=".$fabric['mode_class'].">".$fabric['cc6kxbarModuleChannelFabStatus']."</span></td>
          <td>".formatRates($fabric['cc6kxbarModuleChannelSpeed']*1000000)."</td>
          <td>".print_percentage_bar (125, 20, $percent_in, "Ingress", "ffffff", $background['left'], $percent_in . "%", "ffffff", $background['right'])."</td>
          <td>".print_percentage_bar (125, 20, $percent_out, "Egress", "ffffff", $background['left'], $percent_out . "%", "ffffff", $background['right'])."</td>
          </tr>");

    }
  }

  $c6kxbar_rows++;

}

  echo("</table>");
  echo("</div>");

?>
