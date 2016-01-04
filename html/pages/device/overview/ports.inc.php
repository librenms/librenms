<?php

if ($ports['total']) {
    echo '<div class="container-fluid">';
    echo '<div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
              <strong>Overall Traffic</strong>
            </div>
            <table class="table table-hover table-condensed table-striped">';

    $graph_array['height'] = '100';
    $graph_array['width']  = '485';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['device'] = $device['device_id'];
    $graph_array['type']   = 'device_bits';
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = 'no';
    $graph = generate_lazy_graph_tag($graph_array);

    $link_array         = $graph_array;
    $link_array['page'] = 'graphs';
    unset($link_array['height'], $link_array['width']);
    $link = generate_url($link_array);

    $graph_array['width'] = '210';
    $overlib_content      = generate_overlib_content($graph_array, $device['hostname'].' - Device Traffic');

    echo '<tr>
          <td colspan="4">';
    echo overlib_link($link, $graph, $overlib_content, null);
    echo '  </td>
        </tr>';

    echo '
    <tr>
      <td><img src="images/16/connect.png" align="absmiddle"> '.$ports['total'].'</td>
      <td><img src="images/16/if-connect.png" align="absmiddle"> '.$ports['up'].'</td>
      <td><img src="images/16/if-disconnect.png" align="absmiddle"> '.$ports['down'].'</td>
      <td><img src="images/16/if-disable.png" align="absmiddle"> '.$ports['disabled'].'</td>
    </tr>';

    echo '<tr>
          <td colspan="4">';

    $ifsep = '';
    
    $dataTmp = array();
    if(empty(!$vars['vrf-lite'])){
        $dataTmp = dbFetchRows("SELECT I.* FROM ports I LEFT OUTER JOIN ipv4_addresses I4 on I4.port_id=I.port_id LEFT OUTER JOIN vrf_lite_cisco VR on I4.context_name=VR.context_name and VR.device_id=I.device_id where I.deleted != '1' AND VR.device_id = ? AND VR.vrf_name= ? group by I.ifName order by I.ifName asc", array($device['device_id'],$vars['vrf-lite'])); 
    }
    else{
        $dataTmp = dbFetchRows("SELECT * FROM `ports` WHERE device_id = ? AND `deleted` != '1' ORDER BY `ifName` ASC", array($device['device_id']));
    }
        
    foreach ($dataTmp as $data) {
        $data     = ifNameDescr($data);
        $data = array_merge($data, $device);
        echo "$ifsep".generate_port_link($data, makeshortif(strtolower($data['label'])));
        $ifsep = ', ';
    }
    unset($dataTmp);
    
    unset($ifsep);
    echo '  </td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end if
