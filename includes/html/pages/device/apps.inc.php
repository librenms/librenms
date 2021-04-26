<?php

print_optionbar_start();

echo "<span style='font-weight: bold;'>Apps</span> &#187; ";

unset($sep);

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
];

$app_list = [];
foreach (dbFetchRows('SELECT * FROM `applications` WHERE `device_id` = ?', [$device['device_id']]) as $app) {
    $app['app_display'] = \LibreNMS\Util\StringHelpers::niceCase($app['app_type']);
    $app_list[] = $app;
}

$app_displays = array_column($app_list, 'app_display');
array_multisort($app_displays, SORT_NATURAL | SORT_FLAG_CASE, $app_list);

foreach ($app_list as $app) {
    echo $sep;

    if (! $vars['app']) {
        $vars['app'] = $app['app_type'];
    }

    if ($vars['app'] == $app['app_type']) {
        echo "<span class='pagemenu-selected'>";
    }

    $link_add = ['app' => $app['app_type']];

    $app_state = \LibreNMS\Util\Html::appStateIcon($app['app_state']);
    if (! empty($app_state['icon'])) {
        $text = '<font color="' . $app_state['color'] . '"><i title="' . $app_state['hover_text'] . '" class="fa ' . $app_state['icon'] . ' fa-fw fa-lg" aria-hidden="true"></i></font>';
    } else {
        $text = '';
    }
    $text .= $app['app_display'];

    if (! empty($app['app_instance'])) {
        $text .= '(' . $app['app_instance'] . ')';
        $link_add['instance'] = $app['app_id'];
    }

    echo generate_link($text, $link_array, $link_add);
    if ($vars['app'] == $app['app_type']) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

$where_array = [
    $device['device_id'],
    $vars['app'],
];
if ($vars['instance']) {
    $where = ' AND `app_id` = ?';
    $where_array[] = $vars['instance'];
}

$app = dbFetchRow('SELECT * FROM `applications` WHERE `device_id` = ? AND `app_type` = ?' . $where, $where_array);

if (is_file('includes/html/pages/device/apps/' . $vars['app'] . '.inc.php')) {
    include 'includes/html/pages/device/apps/' . $vars['app'] . '.inc.php';
}

$pagetitle[] = 'Apps';
