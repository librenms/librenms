<?php
echo "<div class='table-responsive'>";
echo "<table id='port-stp' class='table table-condensed table-hover table-striped'>";
echo '<thead>';
echo '<tr>
          <th data-column-id="port">Port</th>
          <th data-column-id="priority">Priority</th>
          <th data-column-id="state">State</th>
          <th data-column-id="enable">Enable</th>
          <th data-column-id="pathCost">Path cost</th>
          <th data-column-id="designatedRoot">Designated root</th>
          <th data-column-id="designatedCost">Designated cost</th>
          <th data-column-id="designatedBridge">Designated bridge</th>
          <th data-column-id="designatedPort">Designated port</th>
          <th data-column-id="forwardTransitions">Fwd trasitions</th>
      </tr>';
echo '</thead>';
echo '<tbody>';

foreach (dbFetchRows("SELECT `ps`.*, `p`.* FROM `ports_stp` `ps` JOIN `ports` `p` ON `ps`.`port_id`=`p`.`port_id` WHERE `ps`.`device_id` = ?", array($device['device_id'])) as $stp_ports_db) {

    $bridge_device = dbFetchRow("SELECT `devices`.*, `stp`.`device_id`, `stp`.`bridgeAddress` FROM `devices` JOIN `stp` ON `devices`.`device_id`=`stp`.`device_id` WHERE `stp`.`bridgeAddress` = ?", array($stp_ports_db['designatedBridge']));
    $root_device = dbFetchRow("SELECT `devices`.*, `stp`.`device_id`, `stp`.`bridgeAddress` FROM `devices` JOIN `stp` ON `devices`.`device_id`=`stp`.`device_id` WHERE `stp`.`bridgeAddress` = ?", array($stp_ports_db['designatedRoot']));

    $stp_ports = [
        generate_port_link($stp_ports_db, $stp_ports_db['ifName'])."<br>".$stp_ports_db['ifAlias'],
        $stp_ports_db['priority'],
        $stp_ports_db['state'],
        $stp_ports_db['enable'],
        $stp_ports_db['pathCost'],
        generate_device_link($root_device, $root_device['hostname'])."<br>".$stp_ports_db['designatedRoot'],
        $stp_ports_db['designatedCost'],
        generate_device_link($bridge_device, $bridge_device['hostname'])."<br>".$stp_ports_db['designatedBridge'],
        $stp_ports_db['designatedPort'],
        $stp_ports_db['forwardTransitions']
    ];
    
    echo "<tr>";
    foreach ($stp_ports as $value) {
       echo "<td>$value</td>";
    }
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '</div>';
// FIXME make table with links and searcheable
// 
?>

<script>
//$("#port-stp").bootgrid();
/*
$("#port-stp").bootgrid( {
    ajax: true,
    post: function () {
        return {
            id: "port-stp"
        };
    },
    url: "ajax_table.php"
});
*/
</script>
