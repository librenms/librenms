<?php

use LibreNMS\Config;

$no_app_id = Config::get('apps.no_app_id');

$name = 'logsize';

if (isset($vars['log_set']) && isset($vars['log_file'])) {
    if ($no_app_id) {
        $filename = Rrd::name($device['hostname'], ['app', $name, $vars['log_set'] . '_____-_____' . $vars['log_file']]);
    } else {
        $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['log_set'] . '_____-_____' . $vars['log_file']]);
    }
} elseif (isset($vars['log_set'])) {
    if ($no_app_id) {
        $filename = Rrd::name($device['hostname'], ['app', $name, $vars['log_set']]);
    } else {
        $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['log_set']]);
    }
} else {
    if ($no_app_id) {
        $filename = Rrd::name($device['hostname'], ['app', $name]);
    } else {
        $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
    }
}
