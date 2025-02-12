<?php

$name = 'php-fpm';
$ds = 'data';

if (isset($vars['phpfpm_pool'])) {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'pools___' . $vars['phpfpm_pool'] . '___' . $stat]);
} else {
    $filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___' . $stat]);
}
