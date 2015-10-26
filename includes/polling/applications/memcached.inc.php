<?php

$data = $agent_data['app']['memcached'][$app['app_instance']];

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-memcached-'.$app['app_id'].'.rrd';

echo 'memcached('.$app['app_instance'].') ';

if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300 
        DS:uptime:GAUGE:600:0:125000000000 
        DS:threads:GAUGE:600:0:125000000000 
        DS:rusage_user_ms:DERIVE:600:0:125000000000 
        DS:rusage_system_ms:DERIVE:600:0:125000000000 
        DS:curr_items:GAUGE:600:0:125000000000 
        DS:total_items:DERIVE:600:0:125000000000 
        DS:limit_maxbytes:GAUGE:600:0:125000000000 
        DS:curr_connections:GAUGE:600:0:125000000000 
        DS:total_connections:DERIVE:600:0:125000000000 
        DS:conn_structures:GAUGE:600:0:125000000000 
        DS:bytes:GAUGE:600:0:125000000000 
        DS:cmd_get:DERIVE:600:0:125000000000 
        DS:cmd_set:DERIVE:600:0:125000000000 
        DS:get_hits:DERIVE:600:0:125000000000 
        DS:get_misses:DERIVE:600:0:125000000000 
        DS:evictions:DERIVE:600:0:125000000000 
        DS:bytes_read:DERIVE:600:0:125000000000 
        DS:bytes_written:DERIVE:600:0:125000000000 
        '.$config['rrd_rra']
    );
}

$fields = array(
    'uptime'            => $data['uptime'],
    'threads'           => $data['threads'],
    'rusage_user_ms'    => $data['rusage_user_microseconds'],
    'rusage_system_ms'  => $data['rusage_system_microseconds'],
    'curr_items'        => $data['curr_items'],
    'total_items'       => $data['total_items'],
    'limit_maxbytes'    => $data['limit_maxbytes'],
    'curr_connections'  => $data['curr_connections'],
    'total_connections' => $data['total_connections'],
    'conn_structures'   => $data['connection_structures'],
    'bytes'             => $data['bytes'],
    'cmd_get'           => $data['cmd_get'],
    'cmd_set'           => $data['cmd_set'],
    'get_hits'          => $data['get_hits'],
    'get_misses'        => $data['get_misses'],
    'evictions'         => $data['evictions'],
    'bytes_read'        => $data['bytes_read'],
    'bytes_written'     => $data['bytes_written'],
);

rrdtool_update($rrd_filename, $fields);

$tags = array('name' => 'memcached', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);

