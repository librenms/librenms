<?php

if (!empty($agent_data['app']['memcached'])) {
    $data = $agent_data['app']['memcached'][$app['app_instance']];
} else {
    $options = '-O qv';
    $oid     = 'nsExtendOutputFull.9.109.101.109.99.97.99.104.101.100';
    $res     = explode(';' , snmp_get($device, $oid, $options));
    $values  = [
        'uptime',
        'threads',
        'rusage_user_microseconds',
        'rusage_system_microseconds',
        'curr_items',
        'total_items',
        'limit_maxbytes',
        'curr_connections',
        'total_connections',
        'connection_structures',
        'bytes',
        'cmd_get',
        'cmd_set',
        'get_hits',
        'get_misses',
        'evictions',
        'bytes_read',
        'bytes_written',
    ];

    $data = array_map(function ($key) use ($res) {
        return substr($res[$key+1], 2);
    },
    array_combine(
        $values,
        array_values(
            array_flip(
                array_filter(
                    $res,
                    function ($item) use ($values) {
                        foreach ($values as $value) {
                            if (strpos($item, $value)) {
                                return true;
                            }
                        }
                    }
                )
            )
        )
    ));
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-memcached-'.$app['app_id'].'.rrd';

echo ' memcached('.$app['app_instance'].')';

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

