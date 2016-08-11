<?php
$rrd = rrd_name($device['hostname'], array('app', 'memcached', $app['app_id']));
if (rrdtool_check_rrd_exists($rrd)) {
    $rrd_filename = $rrd;
}
