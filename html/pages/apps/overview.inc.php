<?php

$graphs['apache'] = array('bits', 'hits', 'scoreboard', 'cpu');
$graphs['drbd']   = array('disk_bits', 'network_bits', 'queue', 'unsynced');
$graphs['mysql']  = array('network_traffic', 'connections', 'command_counters', 'select_types');

$graph_array['height'] = "100";
$graph_array['width']  = "218";
$graph_array['to']     = $now;
$graph_array['from']        = $config['time']['day'];
$graph_array_zoom           = $graph_array;
$graph_array_zoom['height'] = "150";
$graph_array_zoom['width']  = "400";
$graph_array['legend']      = "no";



foreach($app_list as $app)
{
  echo('<div style="clear: both;">');
  echo('<h2>'.generate_link(ucfirst($app['app_type']),array('page'=>'apps','app'=>$app['app_type'])).'</h2>');
  $app_devices = dbFetchRows("SELECT * FROM `devices` AS D, `applications` AS A WHERE D.device_id = A.device_id AND A.app_type = ?", array($app['app_type']));
  foreach($app_devices as $app_device)
  {

    $graph_type = $graphs[$app['app_type']][0];

    $graph_array['type']   = "application_".$app['app_type']."_".$graph_type;
    $graph_array['id']     = $app_device['app_id'];
    $graph_array_zoom['type']   = "application_".$app['app_type']."_".$graph_type;
    $graph_array_zoom['id']     = $app_device['app_id'];

    $link_array = $graph_array;
    $link_array['page'] = "device";
    $link_array['device'] = $app_device['device_id'];
    $link_array['tab'] = "apps";
    $link_array['app']  = $app['app_type'];
    unset($link_array['height'], $link_array['width']);
    $link = generate_url($link_array);

    $overlib_link    = generate_device_link($device, shorthost($app_device['hostname']), array('tab'=>'apps','app'=>$app['app_type']))."<br/>";
    $overlib_link   .= generate_graph_tag($graph_array);
    $overlib_content = generate_overlib_content($graph_array, $port['hostname'] . " - " . $port['label']);

    echo("<div style='display: block; padding: 1px; margin: 2px; min-width: ".$width_div."px; max-width:".$width_div."px; min-height:180px; max-height:180px; 
                      text-align: center; float: left; background-color: #f5f5f5;'>");
    echo(overlib_link($link, $overlib_link, $overlib_content));
    echo("</div>");
  }
  echo('</div>');
}

?>
