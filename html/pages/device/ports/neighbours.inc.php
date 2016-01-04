<?php

$tableNeighbours=array('Local Port','Remote Port','Remote Device','Protocol');
if(!empty($device['vrf_lite_cisco'])){
    $tableNeighbours= array_merge($tableNeighbours,array('VRF','Intance'));
}

echo('<table border="0" cellspacing="0" cellpadding="'.count($tableNeighbours).'" width="100%">');
echo('<tr>');
foreach ($tableNeighbours as $neighbour) {
    echo "<th>$neighbour</th>";
}
echo '</tr>';
unset($tableNeighbours);

$i = '1';

if(!empty($vars['vrf-lite'])){
    $neighboursTmp=dbFetchRows("SELECT L.*, I.*, I4.context_name FROM links AS L join ports AS I on I.port_id = L.local_port_id JOIN ipv4_addresses I4 on I4.port_id=I.port_id join vrf_lite_cisco VR on VR.context_name=I4.context_name and VR.device_id=I.device_id WHERE I.device_id = ? and VR.vrf_name= ? group by L.id ", array($device['device_id'],$vars['vrf-lite']));
}
else{
    $neighboursTmp=dbFetchRows("SELECT L.*, I.*, I4.context_name FROM links AS L, ports AS I LEFT OUTER JOIN ipv4_addresses I4 on I4.port_id=I.port_id WHERE I.device_id = ? AND I.port_id = L.local_port_id", array($device['device_id']));
}


foreach ($neighboursTmp as $neighbour) {
    if ($bg_colour == $list_colour_b) {
        $bg_colour = $list_colour_a;
    }
    else {
        $bg_colour = $list_colour_b;
    }

    echo '<tr bgcolor="'.$bg_colour.'">';
    echo '<td><span style="font-weight: bold;">'.generate_port_link($neighbour).'</span><br />'.$neighbour['ifAlias'].'</td>';

    if (is_numeric($neighbour['remote_port_id']) && $neighbour['remote_port_id']) {
        $remote_port   = get_port_by_id($neighbour['remote_port_id']);
        $remote_device = device_by_id_cache($remote_port['device_id']);
        echo '<td>'.generate_port_link($remote_port).'<br />'.$remote_port['ifAlias'].'</td>';
        echo '<td>'.generate_device_link($remote_device).'<br />'.$remote_device['hardware'].'</td>';
    }
    else {
        echo '<td>'.$neighbour['remote_port'].'</td>';
        echo '<td>'.$neighbour['remote_hostname'].'
          <br />'.$neighbour['remote_platform'].'</td>';
    }

    echo '<td>'.strtoupper($neighbour['protocol']).'</td>';
    if(!empty($device['vrf_lite_cisco'])){
        if(!empty($neighbour['context_name'])){
            if( key_exists($neighbour['context_name'], $device['vrf_lite_cisco'])){
            echo '<td>'.$device['vrf_lite_cisco'][$neighbour['context_name']]['vrf_name'].'</td>';
            echo '<td>'.$device['vrf_lite_cisco'][$neighbour['context_name']]['intance_name'].'</td>';
            }
            else{
                echo '<td>ERROR VRF</td>';
                echo '<td>ERROR VRF</td>';
            }
        }
        else{
            echo '<td></td>';
            echo '<td></td>';
        }
    }
    echo '</tr>';
    $i++;
}//end foreach
unset($neighboursTmp);

echo '</table>';
