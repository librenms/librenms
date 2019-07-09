<?php

$graph_array['height']      = '100';
$graph_array['width']       = '220';
$graph_array['to'] = \LibreNMS\Config::get('time.now');
$graph_array['from'] = \LibreNMS\Config::get('time.day');
$graph_array_zoom           = $graph_array;
$graph_array_zoom['height'] = '150';
$graph_array_zoom['width']  = '400';
$graph_array['legend']      = 'no';

$app_devices = dbFetchRows('SELECT * FROM `devices` AS D, `applications` AS A WHERE D.device_id = A.device_id AND A.app_type = ? ORDER BY hostname', array($vars['app']));

foreach ($app_devices as $app_device) {
    echo '<div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title">
        '.generate_device_link($app_device, shorthost($app_device['hostname']), array('tab' => 'apps', 'app' => $vars['app'])).'
        <div class="pull-right"><small class="muted">'.$app_device['app_instance'].' '.$app_device['app_status'].'</small></div>
        </h3>
        </div>
        <div class="panel-body">
        <div class="row">';

    foreach ($graphs[$vars['app']] as $graph_type) {
        $graph_array['type']      = empty($graph_type) ? 'application_'.$vars['app'] : 'application_'.$vars['app'].'_'.$graph_type;
        $graph_array['id']        = $app_device['app_id'];
        $graph_array_zoom['type'] = 'application_'.$vars['app'].'_'.$graph_type;
        $graph_array_zoom['id']   = $app_device['app_id'];

        $link = generate_url(array('page' => 'device', 'device' => $app_device['device_id'], 'tab' => 'apps', 'app' => $vars['app']));

        echo '<div class="pull-left">';
        echo overlib_link($link, generate_lazy_graph_tag($graph_array), generate_graph_tag($graph_array_zoom), null);
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end foreach

echo '</table>';
