<?php

/*
 * Daily Task Checks
 * (c) 2013 LibreNMS Contributors
 */

require 'includes/defaults.inc.php';
require 'config.php';
require_once 'includes/definitions.inc.php';
require 'includes/functions.php';

$options = getopt('f:');

if ($options['f'] === 'update') {
    $innodb_buffer = innodb_buffer_check();
    if ($innodb_buffer['used'] > $innodb_buffer['size']) {
        if (!empty($config['alert']['default_mail'])) {
            $subject = $config['project_name'] . ' auto-update action required';
            $message = '
Hi,

We have just tried to update your installation but it looks like the InnoDB buffer size is too low.

Because of this we have stopped the auto-update running to ensure your system is ok.

You currently have a configured innodb_buffer_pool_size of ' . $innodb_buffer['size'] / 1024 / 1024 . ' MiB but is currently using ' . $innodb_buffer['used'] / 1024 / 1024 . ' MiB

Take a look at https://dev.mysql.com/doc/refman/5.6/en/innodb-buffer-pool.html for further details.

The ' . $config['project_name'] . ' team.';
            send_mail($config['alert']['default_mail'],$subject,$message,$html=false);
        } 
        echo warn_innodb_buffer($innodb_buffer);
        exit(2);
    }
    else {
        exit((int) $config['update']);
    }
}

if ($options['f'] === 'syslog') {
    if (is_numeric($config['syslog_purge'])) {
        $rows = dbFetchRow('SELECT MIN(seq) FROM syslog');
        while (true) {
            $limit = dbFetchRow('SELECT seq FROM syslog WHERE seq >= ? ORDER BY seq LIMIT 1000,1', array($rows));
            if (empty($limit)) {
                break;
            }

            if (dbDelete('syslog', 'seq >= ? AND seq < ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)', array($rows, $limit, $config['syslog_purge'])) > 0) {
                $rows = $limit;
                echo 'Syslog cleared for entries over '.$config['syslog_purge']." days 1000 limit\n";
            }
            else {
                break;
            }
        }

        dbDelete('syslog', 'seq >= ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)', array($rows, $config['syslog_purge']));
    }
}

if ($options['f'] === 'eventlog') {
    if (is_numeric($config['eventlog_purge'])) {
        if (dbDelete('eventlog', 'datetime < DATE_SUB(NOW(), INTERVAL ? DAY)', array($config['eventlog_purge']))) {
            echo 'Eventlog cleared for entries over '.$config['eventlog_purge']." days\n";
        }
    }
}

if ($options['f'] === 'authlog') {
    if (is_numeric($config['authlog_purge'])) {
        if (dbDelete('authlog', 'datetime < DATE_SUB(NOW(), INTERVAL ? DAY)', array($config['authlog_purge']))) {
            echo 'Authlog cleared for entries over '.$config['authlog_purge']." days\n";
        }
    }
}

if ($options['f'] === 'perf_times') {
    if (is_numeric($config['perf_times_purge'])) {
        if (dbDelete('perf_times', 'start < UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL ? DAY))', array($config['perf_times_purge']))) {
            echo 'Performance poller times cleared for entries over '.$config['perf_times_purge']." days\n";
        }
    }
}

if ($options['f'] === 'callback') {
    include_once 'callback.php';
}

if ($options['f'] === 'device_perf') {
    if (is_numeric($config['device_perf_purge'])) {
        if (dbDelete('device_perf', 'timestamp < UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL ? DAY))', array($config['device_perf_purge']))) {
            echo 'Device performance times cleared for entries over '.$config['device_perf_purge']." days\n";
        }
    }
}

if ($options['f'] === 'notifications') {
    include_once 'notifications.php';
}
