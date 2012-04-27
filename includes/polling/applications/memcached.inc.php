<?php

$data = $agent_data['app']['memcached'][$app['app_instance']];

$rrd_filename  = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-memcached-".$app['app_id'].".rrd";

echo("memcached(".$app['app_instance'].") ");

  if (!is_file($rrd_filename)) {
    rrdtool_create ($rrd_filename, "--step 300 \
        DS:uptime:DERIVE:600:0:125000000000 \
        DS:threads:GAUGE:600:0:125000000000 \
        DS:rusage_user_ms:DERIVE:600:0:125000000000 \
        DS:rusage_system_ms:DERIVE:600:0:125000000000 \
        DS:curr_items:GAUGE:600:0:125000000000 \
        DS:total_items:DERIVE:600:0:125000000000 \
        DS:limit_maxbytes:GAUGE:600:0:125000000000 \
        DS:curr_connections:GAUGE:600:0:125000000000 \
        DS:total_connections:DERIVE:600:0:125000000000 \
        DS:conn_structures:GAUGE:600:0:125000000000 \
        DS:bytes:GAUGE:600:0:125000000000 \
        DS:cmd_get:DERIVE:600:0:125000000000 \
        DS:cmd_set:DERIVE:600:0:125000000000 \
        DS:get_hits:DERIVE:600:0:125000000000 \
        DS:get_misses:DERIVE:600:0:125000000000 \
        DS:evictions:DERIVE:600:0:125000000000 \
        DS:bytes_read:DERIVE:600:0:125000000000 \
        DS:bytes_written:DERIVE:600:0:125000000000 \
        ".$config['rrd_rra']);
  }

  $dslist = array('uptime', 'threads', 'rusage_user_microseconds','rusage_system_microseconds','curr_items','total_items','limit_maxbytes','curr_connections','total_connections',
              'connection_structures','bytes','cmd_get','cmd_set','get_hits','get_misses','evictions','bytes_read','bytes_written');

  $values = array();
  foreach ($dslist as $ds) 
  {
    $values[] = isset($data[$ds]) ? $data[$ds] : -1;
  }

  rrdtool_update($rrd_filename, "N:".implode(":", $values));


?>
