<?php

use App\Models\Application;

$graph_array['height']      = '100';
$graph_array['width']       = '218';
$graph_array['to'] = \LibreNMS\Config::get('time.now');
$graph_array['from'] = \LibreNMS\Config::get('time.day');
$graph_array_zoom           = $graph_array;
$graph_array_zoom['height'] = '150';
$graph_array_zoom['width']  = '400';
$graph_array['legend']      = 'no';

foreach (Application::query()->hasAccess(Auth::user())->with('device')->get()->groupBy('app_type') as $type => $groupedApps) {
    echo '<div style="clear: both;">';
    echo '<h2>'.generate_link($groupedApps->first()->displayName(), array('page' => 'apps', 'app' => $type)).'</h2>';
    /** @var \Illuminate\Support\Collection $groupedApps */
    $groupedApps = $groupedApps->sortBy(function($app) {
        return $app->device->hostname;
    });
    /** @var Application $app */
    foreach ($groupedApps as $app) {
        $graph_type = $graphs[$app->app_type][0];

        $graph_array['type']      = 'application_'.$app->app_type.'_'.$graph_type;
        $graph_array['id']        = $app->app_id;
        $graph_array_zoom['type'] = 'application_'.$app->app_type.'_'.$graph_type;
        $graph_array_zoom['id']   = $app->app_id;

        $link_array           = $graph_array;
        $link_array['page']   = 'device';
        $link_array['device'] = $app->device_id;
        $link_array['tab']    = 'apps';
        $link_array['app']    = $app->app_type;
        unset($link_array['height'], $link_array['width']);
        $overlib_url = generate_url($link_array);

        $overlib_link = '<span style="float:left; margin-left: 10px; font-weight: bold;">'.optional($app->device)->shortDisplayName().'</span>';
        if (!empty($app->app_instance)) {
            $overlib_link             .= '<span style="float:right; margin-right: 10px; font-weight: bold;">'.$app->app_instance.'</span>';
            $content_add = '('.$app->app_instance.')';
        }

        $overlib_link   .= '<br/>';
        $overlib_link   .= generate_graph_tag($graph_array);
        $overlib_content = generate_overlib_content($graph_array, optional($app->device)->displayName().' - '.$app->app_type.$content_add);

        echo "<div style='display: block; padding: 1px; padding-top: 3px; margin: 2px; min-width: ".$width_div.'px; max-width:'.$width_div."px; min-height:165px; max-height:165px;
                      text-align: center; float: left; background-color: #f5f5f5;'>";
        echo overlib_link($overlib_url, $overlib_link, $overlib_content);
        echo '</div>';
    }//end foreach

    echo '</div>';
}//end foreach
