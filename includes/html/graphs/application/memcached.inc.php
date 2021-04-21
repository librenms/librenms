<?php

$rrd = Rrd::name($device['hostname'], ['app', 'memcached', $app['app_id']]);
if (Rrd::checkRrdExists($rrd)) {
    $rrd_filename = $rrd;
}
