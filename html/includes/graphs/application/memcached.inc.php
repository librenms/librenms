<?php
$rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-memcached-".$app['app_id'].".rrd";
if (is_file($rrd))
{
  $rrd_filename = $rrd;
}
?>
