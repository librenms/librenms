<?php

/*
 * Daily Task Checks
 * (c) 2013 LibreNMS Contributors
 */

require 'includes/defaults.inc.php';
require 'config.php';
require_once 'includes/definitions.inc.php';
require 'includes/functions.php';

$options = getopt('f:d');

if (isset($options['d'])) {
    echo "DEBUG\n";
    $debug = true;
}

if ($options['f'] === 'update') {
    if (!$config['update']) {
        exit(0);
    }

    if ($config['update_channel'] == 'master') {
        exit(1);
    }
    elseif ($config['update_channel'] == 'release') {
        exit(3);
    }
    exit(0);
}

if ($options['f'] === 'rrd_purge') {
    if (is_numeric($config['rrd_purge']) && $config['rrd_purge'] > 0) {
        $cmd = "find ".$config['rrd_dir']." -type f -mtime +".$config['rrd_purge']." -print -exec rm -f {} +";
        $purge = `$cmd`;
        if (!empty($purge)) {
            echo "Purged the following RRD files due to old age (over ".$config['rrd_purge']." days old):\n";
            echo $purge;
        }
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
    include_once 'includes/callback.php';
}

if ($options['f'] === 'device_perf') {
    if (is_numeric($config['device_perf_purge'])) {
        if (dbDelete('device_perf', 'timestamp < DATE_SUB(NOW(),INTERVAL ? DAY)', array($config['device_perf_purge']))) {
            echo 'Device performance times cleared for entries over '.$config['device_perf_purge']." days\n";
        }
    }
}

if ($options['f'] === 'notifications') {
    include_once 'includes/notifications.php';
}

if ($options['f'] === 'bill_data') {
    if (is_numeric($config['billing_data_purge']) && $config['billing_data_purge'] > 0) {
        # Deletes data older than XX months before the start of the last complete billing period
        $months = $config['billing_data_purge'];
        echo "Deleting billing data more than $months month before the last completed billing cycle\n";
        $sql = "DELETE bill_data
                FROM bill_data
                    INNER JOIN (SELECT bill_id, 
                        SUBDATE(
                            SUBDATE(
                                ADDDATE(
                                    subdate(curdate(), (day(curdate())-1)),             # Start of this month
                                    bill_day - 1),                                      # Billing anniversary
                                INTERVAL IF(bill_day > DAY(curdate()), 1, 0) MONTH),    # Deal with anniversary not yet happened this month
                            INTERVAL ? MONTH) AS threshold                              # Adjust based on config threshold
                FROM bills) q
                ON bill_data.bill_id = q.bill_id AND bill_data.timestamp < q.threshold;";
        dbQuery($sql, array($months));
    }
}

if ($options['f'] === 'alert_log') {
    if (is_numeric($config['alert_log_purge']) && $config['alert_log_purge'] > 0) {
        if (dbDelete('alert_log', 'time_logged < DATE_SUB(NOW(),INTERVAL ? DAY)', array($config['alert_log_purge']))) {
            echo 'Alert log data cleared for entries over '.$config['alert_log_purge']." days\n";
        }
    }
}

if ($options['f'] === 'purgeusers') {
    $purge = 0;
    if (is_numeric($config['radius']['users_purge']) && $config['auth_mechanism'] === 'radius') {
        $purge = $config['radius']['users_purge'];
    }
    if (is_numeric($config['active_directory']['users_purge']) && $config['auth_mechanism'] === 'active_directory') {
        $purge = $config['active_directory']['users_purge'];
    }
    if ($purge > 0) {
        foreach (dbFetchRows("SELECT DISTINCT(`user`) FROM `authlog` WHERE `datetime` >= DATE_SUB(NOW(), INTERVAL ? DAY)", array($purge)) as $user) {
            $users[] = $user['user'];
        }
        $del_users = '"'.implode('","',$users).'"';
        if (dbDelete('users', "username NOT IN ($del_users)",array($del_users))) {
            echo "Removed users that haven't logged in for $purge days";
        }
    }
}
