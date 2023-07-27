<?php

$name = 'logsize';

if (isset($vars['log_set']) && isset($vars['log_file'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['log_set'] . '_____-_____' . $vars['log_file']]);
} elseif (isset($vars['log_set'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $vars['log_set']]);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}
