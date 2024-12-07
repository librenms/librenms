<?php

use LibreNMS\Util\Clean;
use LibreNMS\Util\Html;

print_optionbar_start();

echo "<span style='font-weight: bold;'>Apps</span> &#187; ";

$sep = '';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
];

$apps = DeviceCache::getPrimary()->applications
    ->sortBy('show_name', SORT_NATURAL | SORT_FLAG_CASE);

foreach ($apps as $current_app) {
    echo $sep;

    if (! $vars['app']) {
        $vars['app'] = $current_app->app_type;
    }

    if ($vars['app'] == $current_app->app_type) {
        $app = $current_app; // set $app for included page
        echo "<span class='pagemenu-selected'>";
    }

    $link_add = ['app' => $current_app->app_type];

    $app_state = Html::appStateIcon($current_app->app_state);
    if (! empty($app_state['icon'])) {
        $text = '<font color="' . $app_state['color'] . '"><i title="' . $app_state['hover_text'] . '" class="fa ' . $app_state['icon'] . ' fa-fw fa-lg" aria-hidden="true"></i></font>';
    } else {
        $text = '';
    }
    $text .= $current_app->displayName();

    if (! empty($current_app->app_instance)) {
        $text .= '(' . $current_app->app_instance . ')';
        $link_add['instance'] = $current_app->app_id;
    }

    echo generate_link($text, $link_array, $link_add);
    if ($vars['app'] == $current_app->app_type) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

$include_file = 'includes/html/pages/device/apps/' . Clean::fileName($app->app_type) . '.inc.php';
if (is_file($include_file)) {
    include $include_file;
}

$pagetitle[] = 'Apps';
