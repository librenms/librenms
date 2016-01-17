<?php
echo "<div style='margin: 0px;'><table class='table'>";
echo '<tr>
          <th><a href="'.generate_url($vars, array('sort' => "port")).'">Port</a></th>
          <th>Priority</th>
          <th>State</th>
          <th>Enable</th>
          <th>Path cost</th>
          <th>Designated root</th>
          <th>Designated cost</th>
          <th>Designated bridge</th>
          <th>Designated port</th>
          <th><a href="'.generate_url($vars, array('sort' => "transitions")).'">Fwd trasitions</a></th>
      </tr>';

switch ($vars["sort"]) {
  case 'transitions':
    $sort = "ps.forwardTransitions DESC";
    break;
  default:
    $sort = "ps.port_id ASC";
    break;
}
$i='1';

// FIXME Table sorting don't working, why?
//echo "$sort";
foreach (dbFetchRows("SELECT `ps`.*, `p`.* FROM `ports_stp` `ps` JOIN `ports` `p` ON `ps`.`port_id`=`p`.`port_id` WHERE `ps`.`device_id` = ? ORDER BY ?", array($device['device_id'], $sort)) as $stp_ports_db) {

    $bridge_device = dbFetchRow("SELECT `devices`.*, `stp`.`device_id`, `stp`.`bridgeAddress` FROM `devices` JOIN `stp` ON `devices`.`device_id`=`stp`.`device_id` WHERE `stp`.`bridgeAddress` = ?", array($stp_ports_db['designatedBridge']));
    $root_device = dbFetchRow("SELECT `devices`.*, `stp`.`device_id`, `stp`.`bridgeAddress` FROM `devices` JOIN `stp` ON `devices`.`device_id`=`stp`.`device_id` WHERE `stp`.`bridgeAddress` = ?", array($stp_ports_db['designatedRoot']));

    $stp_ports = [
        generate_port_link($stp_ports_db, $stp_ports_db['ifName'])."<br>".$stp_ports_db['ifAlias'],
        $stp_ports_db['priority'],
        $stp_ports_db['state'],
        $stp_ports_db['enable'],
        $stp_ports_db['pathCost'],
        //$stp_ports_db['designatedRoot'],
        generate_device_link($root_device, $root_device['hostname'])."<br>".$stp_ports_db['designatedRoot'],
        $stp_ports_db['designatedCost'],
        generate_device_link($bridge_device, $bridge_device['hostname'])."<br>".$stp_ports_db['designatedBridge'],
        $stp_ports_db['designatedPort'],
        $stp_ports_db['forwardTransitions']
    ];
    $i++;
    if (!is_integer($i / 2)) {
            $row_colour = $list_colour_b;
    }
    else {
            $row_colour = $list_colour_a;
    }
    echo "<tr style='background-color: $row_colour;'>";

    foreach ($stp_ports as $value) {
       echo "<td>$value</td>";
    }

    echo '</tr>';
}
