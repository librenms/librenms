<?php

if ($device['os_group'] == 'unix') {
    echo $config['project_name'].' UNIX Agent: ';

    $agent_port = get_dev_attrib($device,'override_Unixagent_port');
    if (empty($agent_port)) {
        $agent_port = $config['unix-agent']['port'];
    }
    if (empty($config['unix-agent']['connection-timeout'])) {
        $config['unix-agent']['connection-timeout'] = $config['unix-agent-connection-time-out'];
    }
    if (empty($config['unix-agent']['read-timeout'])) {
        $config['unix-agent']['read-timeout'] = $config['unix-agent-read-time-out'];
    }

    $agent_start = microtime(true);
    $agent       = fsockopen($device['hostname'], $agent_port, $errno, $errstr, $config['unix-agent']['connection-timeout']);

    // Set stream timeout (for timeouts during agent  fetch
    stream_set_timeout($agent, $config['unix-agent']['read-timeout']);
    $agentinfo = stream_get_meta_data($agent);

    if (!$agent) {
        echo 'Connection to UNIX agent failed on port '.$port.'.';
    }
    else {
        // fetch data while not eof and not timed-out
        while ((!feof($agent)) && (!$agentinfo['timed_out'])) {
            $agent_raw .= fgets($agent, 128);
            $agentinfo  = stream_get_meta_data($agent);
        }

        if ($agentinfo['timed_out']) {
            echo 'Connection to UNIX agent timed out during fetch on port '.$port.'.';
        }
    }

    $agent_end  = microtime(true);
    $agent_time = round(($agent_end - $agent_start) * 1000);

    if (!empty($agent_raw)) {
        echo 'execution time: '.$agent_time.'ms';
        $agent_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/agent.rrd';
        if (!is_file($agent_rrd)) {
            rrdtool_create($agent_rrd, 'DS:time:GAUGE:600:0:U '.$config['rrd_rra']);
        }

        $fields = array(
            'time' => $agent_time,
        );
 
        rrdtool_update($agent_rrd, $fields);

        $tags = array();
        influx_update($device,'agent',$tags,$fields);

        $graphs['agent'] = true;

        foreach (explode('<<<', $agent_raw) as $section) {
            list($section, $data) = explode('>>>', $section);
            list($sa, $sb)    = explode('-', $section, 2);

            $agentapps = array(
                "apache",
                "ceph",
                "mysql",
                "nginx",
                "bind",
                "powerdns",
                "proxmox",
                "tinydns");

            if (in_array($section, $agentapps)) {
                $agent_data['app'][$section] = trim($data);
            }

            if (!empty($sa) && !empty($sb)) {
                $agent_data[$sa][$sb] = trim($data);
            }
            else {
                $agent_data[$section] = trim($data);
            }
        }//end foreach

        d_echo($agent_data);

        include 'unix-agent/packages.inc.php';
        include 'unix-agent/munin-plugins.inc.php';

        foreach (array_keys($agent_data) as $key) {
            if (file_exists("includes/polling/unix-agent/$key.inc.php")) {
                d_echo("Including: unix-agent/$key.inc.php");

                include "unix-agent/$key.inc.php";
            }
        }

        // Processes
        if (!empty($agent_data['ps'])) {
            echo 'Processes: ';
            dbDelete('processes', 'device_id = ?', array($device['device_id']));
            $data=array();
            foreach (explode("\n", $agent_data['ps']) as $process) {
                $process = preg_replace('/\((.*),([0-9]*),([0-9]*),([0-9\:]*),([0-9]*)\)\ (.*)/', '\\1|\\2|\\3|\\4|\\5|\\6', $process);
                list($user, $vsz, $rss, $cputime, $pid, $command) = explode('|', $process, 6);
                if (!empty($command)) {
                    $data[]=array('device_id' => $device['device_id'], 'pid' => $pid, 'user' => $user, 'vsz' => $vsz, 'rss' => $rss, 'cputime' => $cputime, 'command' => $command);
                }
            }
            if (count($data) > 0) {
                dbBulkInsert('processes',$data);
            }
            echo "\n";
        }

        foreach (array_keys($agent_data['app']) as $key) {
            if (file_exists("includes/polling/applications/$key.inc.php")) {
                d_echo("Enabling $key for ".$device['hostname']." if not yet enabled\n");

                if (in_array($key, array('apache', 'mysql', 'nginx', 'proxmox', 'ceph', 'powerdns'))) {
                    if (dbFetchCell('SELECT COUNT(*) FROM `applications` WHERE `device_id` = ? AND `app_type` = ?', array($device['device_id'], $key)) == '0') {
                        echo "Found new application '$key'\n";
                        dbInsert(array('device_id' => $device['device_id'], 'app_type' => $key, 'app_status' => '', 'app_instance' => ''), 'applications');
                    }
                }
            }
        }

        // memcached
        if (!empty($agent_data['app']['memcached'])) {
            $agent_data['app']['memcached'] = unserialize($agent_data['app']['memcached']);
            foreach ($agent_data['app']['memcached'] as $memcached_host => $memcached_data) {
                if (dbFetchCell('SELECT COUNT(*) FROM `applications` WHERE `device_id` = ? AND `app_type` = ? AND `app_instance` = ?', array($device['device_id'], 'memcached', $memcached_host)) == '0') {
                    echo "Found new application 'Memcached' $memcached_host\n";
                    dbInsert(array('device_id' => $device['device_id'], 'app_type' => 'memcached', 'app_status' => '', 'app_instance' => $memcached_host), 'applications');
                }
            }
        }

        // DRBD
        if (!empty($agent_data['drbd'])) {
            $agent_data['app']['drbd'] = array();
            foreach (explode("\n", $agent_data['drbd']) as $drbd_entry) {
                list($drbd_dev, $drbd_data) = explode(':', $drbd_entry);
                if (preg_match('/^drbd/', $drbd_dev)) {
                    $agent_data['app']['drbd'][$drbd_dev] = $drbd_data;
                    if (dbFetchCell('SELECT COUNT(*) FROM `applications` WHERE `device_id` = ? AND `app_type` = ? AND `app_instance` = ?', array($device['device_id'], 'drbd', $drbd_dev)) == '0') {
                        echo "Found new application 'DRBd' $drbd_dev\n";
                        dbInsert(array('device_id' => $device['device_id'], 'app_type' => 'drbd', 'app_status' => '', 'app_instance' => $drbd_dev), 'applications');
                    }
                }
            }
        }
    }//end if

    if (!empty($agent_sensors)) {
        echo 'Sensors: ';
        check_valid_sensors($device, 'temperature', $valid['sensor'], 'agent');
        echo "\n";
    }

    echo "\n";
}//end if
