<?php

print_optionbar_start();

echo("<a href='".$config['base_url']."/pseudowires/'>Details</a> | Graphs : 
<a href='".$config['base_url']."/pseudowires/graphs/mini/'>Mini</a>
");

print_optionbar_end();

 list($opta, $optb, $optc, $optd, $opte) = explode("/", $_GET['opta']);

echo("<table cellpadding=5 cellspacing=0 class=devicetable width=100%>");

$sql = "SELECT * FROM pseudowires AS P, interfaces AS I, devices AS D WHERE P.interface_id = I.interface_id AND I.device_id = D.device_id ORDER BY D.hostname,I.ifDescr";
$query = mysql_query($sql);

while($pw_a = mysql_fetch_array($query)) {
   $i = 0;
   while ($i < count($linkdone)) {
      $thislink = $pw_a['device_id'] . $pw_a['interface_id'];
      if ($linkdone[$i] == $thislink) { $skip = "yes"; }
      $i++;
  }

  $pw_b = mysql_fetch_array(mysql_query("SELECT * from `devices` AS D, `interfaces` AS I, `pseudowires` AS P WHERE D.device_id = '".$pw_a['peer_device_id']."' AND
                                                                                                          D.device_id = I.device_id AND
                                                                                                          P.cpwVcID = '".$pw_a['cpwVcID']."' AND
                                                                                                          P.interface_id = I.interface_id"));

  if(!interfacepermitted($pw_a['interface_id'])) { $skip = "yes"; }
  if(!interfacepermitted($pw_b['interface_id'])) { $skip = "yes"; }


  if($skip) {
    unset($skip);
  } else {

    if($bg == "ffffff") { $bg = "e5e5e5"; } else { $bg="ffffff"; }
    echo("<tr style=\"background-color: #$bg;\"><td rowspan=2 style='font-size:18px; padding:4px;'>".$pw_a['cpwVcID']."</td><td>".generatedevicelink($pw_a)."</td><td>".generateiflink($pw_a)."</td>
                                                                                          <td rowspan=2> <img src='".$config['base_url']."/images/16/arrow_right.png'> </td>
                                                                                          <td>".generatedevicelink($pw_b)."</td><td>".generateiflink($pw_b)."</td></tr>");
    echo("<tr style=\"background-color: #$bg;\"><td colspan=2>".$pw_a['ifAlias']."</td><td colspan=2>".$pw_b['ifAlias']."</td></tr>");
    if($opta == "graphs") {
      echo("<tr style=\"background-color: #$bg;\"><td></td><td colspan=2>");
      if(!$optb) { $optb = "mini"; }
     if($pw_a) {
        $pw_a['width'] = "150";
        $pw_a['height'] = "30";
        $pw_a['from'] = $day;
        $pw_a['to'] = $now;
        $pw_a['bg'] = $bg;
        $types = array('bits','pkts','errors');
        foreach($types as $graph_type) {
          $pw_a['graph_type'] = $graph_type;
          generate_port_thumbnail($pw_a);
        }
      }
      echo("</td><td></td><td colspan=2>");

      if($pw_b) {
        $pw_b['width'] = "150";
        $pw_b['height'] = "30";
        $pw_b['from'] = $day;
        $pw_b['to'] = $now;
        $pw_b['bg'] = $bg;
        $types = array('bits','pkts','errors');
        foreach($types as $graph_type) {
          $pw_b['graph_type'] = $graph_type;
          generate_port_thumbnail($pw_b);
        }
      }

      echo("</td></tr>");
  
    }

    $linkdone[] = $pw_b['device_id'] . $pw_b['interface_id'];
  }

}

echo("</table>");

?>
