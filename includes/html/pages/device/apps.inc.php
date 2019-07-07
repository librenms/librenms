<?php

print_optionbar_start();

echo "<span style='font-weight: bold;'>Apps</span> &#187; ";

unset($sep);

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
);

foreach (dbFetchRows('SELECT * FROM `applications` WHERE `device_id` = ? ORDER BY `app_type` ASC', array($device['device_id'])) as $app) {
    echo $sep;

    if (!$vars['app']) {
        $vars['app'] = $app['app_type'];
    }

    if ($vars['app'] == $app['app_type']) {
        echo "<span class='pagemenu-selected'>";
    } else {
    }

    $link_add = array('app' => $app['app_type']);
    $text     = nicecase($app['app_type']);
    if (!empty($app['app_instance'])) {
        $text                .= '('.$app['app_instance'].')';
        $link_add['instance'] = $app['app_id'];
    }

    echo generate_link($text, $link_array, $link_add);
    if ($vars['app'] == $app['app_type']) {
        echo '</span>';
    }

    $sep = ' | ';
}

print_optionbar_end();

$where_array = array(
    $device['device_id'],
    $vars['app'],
);
if ($vars['instance']) {
    $where         = ' AND `app_id` = ?';
    $where_array[] = $vars['instance'];
}

$app = dbFetchRow('SELECT * FROM `applications` WHERE `device_id` = ? AND `app_type` = ?'.$where, $where_array);

if (is_file('includes/html/pages/device/apps/'.mres($vars['app']).'.inc.php')) {
    include 'includes/html/pages/device/apps/'.mres($vars['app']).'.inc.php';
}

$pagetitle[] = 'Apps';
