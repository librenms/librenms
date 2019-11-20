<?php

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'unix') {
    echo \LibreNMS\Config::get('project_name') . ' UNIX Agent: ';

    $agent_port = get_dev_attrib($device, 'override_Unixagent_port');
    if (empty($agent_port)) {
        $agent_port = \LibreNMS\Config::get('unix-agent.port');
    }

    $agent_start = microtime(true);
    $agent = fsockopen($device['hostname'], $agent_port, $errno, $errstr, \LibreNMS\Config::get('unix-agent.connection-timeout'));

    // Set stream timeout (for timeouts during agent  fetch
    stream_set_timeout($agent, \LibreNMS\Config::get('unix-agent.read-timeout'));
    $agentinfo = stream_get_meta_data($agent);

    if (!$agent) {
        echo 'Connection to UNIX agent failed on port '.$agent_port.'.';
    } else {
        // fetch data while not eof and not timed-out
        while ((!feof($agent)) && (!$agentinfo['timed_out'])) {
            $agent_raw .= fgets($agent, 128);
            $agentinfo  = stream_get_meta_data($agent);
        }

        if ($agentinfo['timed_out']) {
            echo 'Connection to UNIX agent timed out during fetch on port '.$agent_port.'.';
        }
    }

    $agent_end  = microtime(true);
    $agent_time = round(($agent_end - $agent_start) * 1000);

    if (!empty($agent_raw)) {
        echo 'execution time: '.$agent_time.'ms';

        $tags = array(
            'rrd_def' => RrdDefinition::make()->addDataset('time', 'GAUGE', 0),
        );
        $fields = array(
            'time' => $agent_time,
        );
        data_update($device, 'agent', $tags, $fields);

        $graphs['agent'] = true;

        $agentapps = array(
            "apache",
            "bind",
            "ceph",
            "mysql",
            "nginx",
            "powerdns",
            "powerdns-recursor",
            "proxmox",
            "rrdcached",
            "tinydns",
            "gpsd",
          );

        foreach (explode('<<<', $agent_raw) as $section) {
            list($section, $data) = explode('>>>', $section);
            list($sa, $sb)    = explode('-', $section, 2);

            if (in_array($section, $agentapps)) {
                $agent_data['app'][$section] = trim($data);
            }

            if (!empty($sa) && !empty($sb)) {
                $agent_data[$sa][$sb] = trim($data);
            } else {
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
                $process = preg_replace('/\((.*),([0-9]*),([0-9]*),([0-9\:\.\-]*),([0-9]*)\)\ (.*)/', '\\1|\\2|\\3|\\4|\\5|\\6', $process);
                list($user, $vsz, $rss, $cputime, $pid, $command) = explode('|', $process, 6);
                if (!empty($command)) {
                    $data[]=array('device_id' => $device['device_id'], 'pid' => $pid, 'user' => $user, 'vsz' => $vsz, 'rss' => $rss, 'cputime' => $cputime, 'command' => $command);
                }
            }
            if (count($data) > 0) {
                dbBulkInsert($data, 'processes');
            }
            echo "\n";
        }

        foreach (array_keys($agent_data['app']) as $key) {
            if (file_exists("includes/polling/applications/$key.inc.php")) {
                d_echo("Enabling $key for ".$device['hostname']." if not yet enabled\n");

                if (in_array($key, $agentapps)) {
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
        d_echo($agent_sensors);
        if (count($agent_sensors) > 0) {
            record_sensor_data($device, $agent_sensors);
        }
        echo "\n";
    }

    echo "\n";
}//end if
