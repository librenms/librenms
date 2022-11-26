<?php

$name = 'linux_iw';

$rrd_identifier = $vars['interface'] ?? '';

if (! isset($vars['interface'])) {
    $interface_list = Rrd::getRrdApplicationArrays($device, $app->app_id, $name);
    $rrd_identifier = end($interface_list) ?? '';
}
