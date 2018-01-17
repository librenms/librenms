#!/usr/bin/env php
<?php

/*
 * Daily Task Checks
 * (c) 2013 LibreNMS Contributors
 */

use LibreNMS\Config;
use LibreNMS\Exceptions\LockException;
use LibreNMS\Util\MemcacheLock;

$init_modules = array('alerts');
require __DIR__ . '/includes/init.php';
include_once __DIR__ . '/includes/notifications.php';

$options = getopt('df:o:t:r:');

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
    } elseif ($config['update_channel'] == 'release') {
        exit(3);
    }
    exit(0);
}

if ($options['f'] === 'rrd_purge') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('rrd_purge', 0, 86000);
        }

        $rrd_purge = Config::get('rrd_purge');
        $rrd_dir = Config::get('rrd_dir');

        if (is_numeric($rrd_purge) && $rrd_purge > 0) {
            $cmd = "find $rrd_dir -type f -mtime +$rrd_purge -print -exec rm -f {} +";
            $purge = `$cmd`;
            if (!empty($purge)) {
                echo "Purged the following RRD files due to old age (over $rrd_purge days old):\n";
                echo $purge;
            }
        }
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'syslog') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('syslog_purge', 0, 86000);
        }
        $syslog_purge = Config::get('syslog_purge');

        if (is_numeric($syslog_purge)) {
            $rows = (int)dbFetchCell('SELECT MIN(seq) FROM syslog');
            while (true) {
                $limit = dbFetchRow('SELECT seq FROM syslog WHERE seq >= ? ORDER BY seq LIMIT 1000,1', array($rows));
                if (empty($limit)) {
                    break;
                }

                if (dbDelete('syslog', 'seq >= ? AND seq < ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)', array($rows, $limit, $syslog_purge)) > 0) {
                    $rows = $limit;
                    echo "Syslog cleared for entries over $syslog_purge days 1000 limit\n";
                } else {
                    break;
                }
            }

            dbDelete('syslog', 'seq >= ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)', array($rows, $syslog_purge));
        }
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'eventlog') {
    $ret = lock_and_purge('eventlog', 'datetime < DATE_SUB(NOW(), INTERVAL ? DAY)');
    exit($ret);
}

if ($options['f'] === 'authlog') {
    $ret = lock_and_purge('authlog', 'datetime < DATE_SUB(NOW(), INTERVAL ? DAY)');
    exit($ret);
}

if ($options['f'] === 'perf_times') {
    $ret = lock_and_purge('perf_times', 'start < UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL ? DAY))');
    exit($ret);
}

if ($options['f'] === 'callback') {
    include_once 'includes/callback.php';
}

if ($options['f'] === 'device_perf') {
    $ret = lock_and_purge('device_perf', 'timestamp < DATE_SUB(NOW(),INTERVAL ? DAY)');
    exit($ret);
}

if ($options['f'] === 'handle_notifiable') {
    if ($options['t'] === 'update') {
        $title = 'Error: Daily update failed';
        $poller_name = Config::get('distributed_poller_name');

        if ($options['r']) {
            // result was a success (1), remove the notification
            remove_notification($title);
        } else {
            // result was a failure (0), create the notification
            new_notification(
                $title,
                "The daily update script (daily.sh) has failed on $poller_name."
                . 'Please check output by hand. If you need assistance, '
                . 'visit the <a href="https://www.librenms.org/#support">LibreNMS Website</a> to find out how.',
                2,
                'daily.sh'
            );
        }
    } elseif ($options['t'] === 'phpver') {
        $error_title = 'Error: PHP version too low';
        $warn_title = 'Warning: PHP version too low';
        remove_notification($warn_title); // remove warning

        // if update is not set to false and version is min or newer
        if (Config::get('update') && $options['r']) {
            new_notification(
                $error_title,
                'PHP version 5.6.4 is the minimum supported version as of January 10, 2018.  We recommend you update to PHP a supported version of PHP (7.1 suggested) to continue to receive updates.  If you do not update PHP, LibreNMS will continue to function but stop receiving bug fixes and updates.',
                2,
                'daily.sh'
            );
            exit(1);
        }

        remove_notification($error_title);
        exit(0);
    }
}

if ($options['f'] === 'notifications') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('notifications', 0, 86000);
        }

        post_notifications();
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'bill_data') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('syslog_purge', 0, 86000);
        }
        $billing_data_purge = Config::get('billing_data_purge');
        if (is_numeric($billing_data_purge) && $billing_data_purge > 0) {
            # Deletes data older than XX months before the start of the last complete billing period
            echo "Deleting billing data more than $billing_data_purge month before the last completed billing cycle\n";
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
            dbQuery($sql, array($billing_data_purge));
        }
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'alert_log') {
    $ret = lock_and_purge('alert_log', 'time_logged < DATE_SUB(NOW(),INTERVAL ? DAY)');
    exit($ret);
}

if ($options['f'] === 'purgeusers') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('purgeusers', 0, 86000);
        }

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
            $del_users = '"'.implode('","', $users).'"';
            if (dbDelete('users', "username NOT IN ($del_users)", array($del_users))) {
                echo "Removed users that haven't logged in for $purge days";
            }
        }
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'refresh_alert_rules') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('refresh_alert_rules', 0, 86000);
        }

        echo 'Refreshing alert rules queries' . PHP_EOL;
        $rules = dbFetchRows('SELECT `id`, `rule` FROM `alert_rules`');
        foreach ($rules as $rule) {
            $data['query'] = GenSQL($rule['rule']);
            if (!empty($data['query'])) {
                dbUpdate($data, 'alert_rules', 'id=?', array($rule['id']));
                unset($data);
            }
        }
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'notify') {
    if (isset($config['alert']['default_mail'])) {
        send_mail(
            $config['alert']['default_mail'],
            '[LibreNMS] Auto update has failed',
            "We just attempted to update your install but failed. The information below should help you fix this.\r\n\r\n" . $options['o']
        );
    }
}

if ($options['f'] === 'peeringdb') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('peeringdb', 0, 86000);
        }
        cache_peeringdb();
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'refresh_os_cache') {
    echo 'Clearing OS cache' . PHP_EOL;
    unlink(Config::get('install_dir') . '/cache/os_defs.cache');
}
