<?php

echo " rrdcached";

$data = "";

if($agent_data['app']['rrdcached']) {
    $data = $agent_data['app']['rrdcached'];
} else {
    d_echo("\nNo Agent Data. Attempting to connect directly to the rrdcached server ".$device['hostname'].":42217\n");

    $sock = fsockopen($device['hostname'], 42217, $errno, $errstr, 5);

    if(!$sock && $device['hostname'] == 'localhost') {
        if (file_exists('/var/run/rrdcached.sock')) {
	    $sock = fsockopen('unix:///var/run/rrdcached.sock');
	} elseif (file_exists('/run/rrdcached.sock')) {
	    $sock = fsockopen('unix:///run/rrdcached.sock');
        } elseif (file_exists('/tmp/rrdcached.sock')) {
	    $sock = fsockopen('unix:///tmp/rrdcached.sock');
        }
    }

    if($sock) {
        fwrite($sock, "STATS\n");
        $max = -1;
        $count = 0;
        while ($max == -1 || $count < $max) {
            $data .= fgets($sock, 128);
            if ($max == -1) {
                $max = explode(' ', $data)[0] + 1;
            }
            $count++;
        }
        fclose($sock);
    } else {
        d_echo("ERROR: $errno - $errstr\n");
    }
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-rrdcached-'.$app['app_id'].'.rrd';


if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300
        DS:queue_length:GAUGE:600:0:U
        DS:updates_received:COUNTER:600:0:U
        DS:flushes_received:COUNTER:600:0:U
        DS:updates_written:COUNTER:600:0:U
        DS:data_sets_written:COUNTER:600:0:U
        DS:tree_nodes_number:GAUGE:600:0:U
        DS:tree_depth:GAUGE:600:0:U
        DS:journal_bytes:COUNTER:600:0:U
        DS:journal_rotate:COUNTER:600:0:U
        '.$config['rrd_rra']
    );
}
$fields = array();
foreach (explode("\n", $data) as $line) {
    $split = explode(': ', $line);
    if (count($split) == 2) {
        $name = strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($split[0])));
        $fields[$name] = $split[1];
    }
}

rrdtool_update($rrd_filename, $fields);

$tags = array('name' => 'rrdcached', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);

unset($data);
unset($rrd_filename);
unset($fields);
unset($tags);
