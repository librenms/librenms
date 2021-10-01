<?php

use App\Models\Application;
use LibreNMS\Util\Url;

$graph_array['height'] = '100';
$graph_array['width'] = '220';
$graph_array['to'] = \LibreNMS\Config::get('time.now');
$graph_array['from'] = \LibreNMS\Config::get('time.day');
$graph_array_zoom = $graph_array;
$graph_array_zoom['height'] = '150';
$graph_array_zoom['width'] = '400';
$graph_array['legend'] = 'no';

$apps = Application::query()->hasAccess(Auth::user())->where('app_type', $vars['app'])->with('device')->get()->sortBy(function ($app) {
    return $app->device->hostname;
});

foreach ($apps as $app) {
    $app_state = \LibreNMS\Util\Html::appStateIcon($app['app_state']);
    if (! empty($app_state['icon'])) {
        $app_state_info = '<font color="' . $app_state['color'] . '"><i title="' . $app_state['hover_text'] . '" class="fa ' . $app_state['icon'] . ' fa-fw fa-lg" aria-hidden="true"></i></font>';
    } else {
        $app_state_info = '';
    }

    echo '<div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title">' .
        $app_state_info .
        Url::deviceLink($app->device, null, ['tab' => 'apps', 'app' => $app->app_type]) . '
        <div class="pull-right"><small class="muted">' . $app->app_instance . ' ' . $app->app_status . '</small></div>
        </h3>
        </div>
        <div class="panel-body">
        <div class="row">';

    foreach ($graphs[$app->app_type] as $graph_type) {
        $graph_array['type'] = empty($graph_type) ? 'application_' . $app->app_type : 'application_' . $app->app_type . '_' . $graph_type;
        $graph_array['id'] = $app->app_id;
        $graph_array_zoom['type'] = 'application_' . $app->app_type . '_' . $graph_type;
        $graph_array_zoom['id'] = $app->app_id;

        $link = Url::generate(['page' => 'device', 'device' => $app->device_id, 'tab' => 'apps', 'app' => $app->app_type]);

        echo '<div class="pull-left">';
        echo Url::overlibLink($link, Url::lazyGraphTag($graph_array), Url::graphTag($graph_array_zoom));
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end foreach

echo '</table>';
