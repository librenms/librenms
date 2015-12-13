<?php

$link_array = array(
               'page'   => 'device',
               'device' => $device['device_id'],
               'tab'    => 'vlans',
              );

print_optionbar_start();

echo "<span style='font-weight: bold;'>VLANs</span> &#187; ";

if ($vars['view'] == 'graphs' || $vars['view'] == 'minigraphs') {
    if (isset($vars['graph'])) {
        $graph_type = 'port_'.$vars['graph'];
    }
    else {
        $graph_type = 'port_bits';
    }
}

if (!$vars['view']) {
    $vars['view'] = 'basic';
}

$menu_options['basic'] = 'Basic';
// $menu_options['details'] = 'Details';
$sep = '';
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $link_array, array('view' => $option));
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

echo ' | Graphs: ';

$graph_types = array(
                'bits'   => 'Bits',
                'upkts'  => 'Unicast Packets',
                'nupkts' => 'Non-Unicast Packets',
                'errors' => 'Errors',
               );

foreach ($graph_types as $type => $descr) {
    echo "$type_sep";
    if ($vars['graph'] == $type && $vars['view'] == 'graphs') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($descr, $link_array, array('view' => 'graphs', 'graph' => $type));
    if ($vars['graph'] == $type && $vars['view'] == 'graphs') {
        echo '</span>';
    }

    /*
        echo(' (');
        if ($vars['graph'] == $type && $vars['type'] == "minigraphs") { echo("<span class='pagemenu-selected'>"); }
        echo(generate_link('Mini',$link_array,array('type'=>'minigraphs','graph'=>$type)));
        if ($vars['graph'] == $type && $vars['type'] == "minigraphs") { echo("</span>"); }
        echo(')');
    */
    $type_sep = ' | ';
}

print_optionbar_end();

echo '<table border="0" cellspacing="0" cellpadding="5" width="100%">';

$i = '1';
if(empty($vars['vrf-lite'])){
    $vlansTmp=dbFetchRows("SELECT * FROM `vlans` V WHERE `device_id` = ? ORDER BY 'vlan_vlan'", array($device['device_id']));
}
else{
    $vlansTmp=dbFetchRows("SELECT V.* FROM `vlans` V join ports_vlans PV on PV.vlan=V.vlan_vlan and PV.device_id=V.device_id join ports I on I.port_id=PV.port_id join vrf_lite_cisco VR on PV.device_id=VR.device_id join ipv4_addresses I4A on I4A.context_name=VR.context_name and I.port_id=I4A.port_id and I.device_id=VR.device_id AND V.device_id=I.device_id where V.device_id = ? and VR.vrf_name = ?", array($device['device_id'],$vars['vrf-lite']));
}

foreach ($vlansTmp as $vlan) {
    include 'includes/print-vlan.inc.php';

    $i++;
}
unset($vlansTmp);
echo '</table>';

$pagetitle[] = 'VLANs';
