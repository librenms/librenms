<?php
$rrd = rrd_name($device['hostname'], array('app', 'memcached', $app['app_id']));
if (is_file($rrd)) {
    $rrd_filename = $rrd;
}
