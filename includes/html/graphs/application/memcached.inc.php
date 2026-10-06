<?php

$rrd = Rrd::name($device['hostname'], ['app', 'memcached', $app->app_id]);
$rrd_filename = $rrd;
