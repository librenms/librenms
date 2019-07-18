#!/usr/bin/env php
<?php

/*
 * Daily Task Checks
 * (c) 2013 LibreNMS Contributors
 */

use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Alert\AlertDB;
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
    if (!Config::get('update')) {
        exit(0);
    }

    if (Config::get('update_channel') == 'master') {
        exit(1);
    } elseif (Config::get('update_channel') == 'release') {
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

if ($options['f'] === 'ports_fdb') {
    $ret = lock_and_purge('ports_fdb', 'updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
    exit($ret);
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

if ($options['f'] === 'ports_purge') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('ports_purge', 0, 86000);
        }
        $ports_purge = Config::get('ports_purge');

        if ($ports_purge) {
            $interfaces = dbFetchRows('SELECT * from `ports` AS P, `devices` AS D WHERE `deleted` = 1 AND D.device_id = P.device_id');
            foreach ($interfaces as $interface) {
                delete_port($interface['port_id']);
            }
            echo "All deleted ports now purged\n";
        }
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
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
            if ($options['r'] === 'php53') {
                $phpver   = '5.6.4';
                $eol_date = 'January 10th, 2018';
            } elseif ($options['r'] === 'php56') {
                $phpver   = '7.1.3';
                $eol_date = 'February 1st, 2019';
            }
            if (isset($phpver)) {
                new_notification(
                    $error_title,
                    "PHP version $phpver is the minimum supported version as of $eol_date.  We recommend you update to PHP a supported version of PHP (7.2 suggested) to continue to receive updates.  If you do not update PHP, LibreNMS will continue to function but stop receiving bug fixes and updates.",
                    2,
                    'daily.sh'
                );
                exit(1);
            }
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
        if (is_numeric(\LibreNMS\Config::get('radius.users_purge')) && Config::get('auth_mechanism') === 'radius') {
            $purge = \LibreNMS\Config::get('radius.users_purge');
        }
        if (is_numeric(\LibreNMS\Config::get('active_directory.users_purge')) && Config::get('auth_mechanism') === 'active_directory') {
            $purge = \LibreNMS\Config::get('active_directory.users_purge');
        }
        if ($purge > 0) {
            foreach (dbFetchRows("SELECT DISTINCT(`user`) FROM `authlog` WHERE `datetime` >= DATE_SUB(NOW(), INTERVAL ? DAY)", array($purge)) as $user) {
                $users[] = $user['user'];
            }

            if (dbDelete('users', "username NOT IN " . dbGenPlaceholders(count($users)), $users)) {
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
        $rules = dbFetchRows('SELECT `id`, `rule`, `builder`, `extra` FROM `alert_rules`');
        foreach ($rules as $rule) {
            $rule_options = json_decode($rule['extra'], true);
            if ($rule_options['options']['override_query'] !== 'on') {
                $data['query'] = AlertDB::genSQL($rule['rule'], $rule['builder']);
                if (!empty($data['query'])) {
                    dbUpdate($data, 'alert_rules', 'id=?', array($rule['id']));
                    unset($data);
                }
            }
        }
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'refresh_device_groups') {
    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('refresh_device_groups', 0, 86000);
        }

        echo 'Refreshing device group table relationships' . PHP_EOL;
        DeviceGroup::all()->each(function ($deviceGroup) {
            if ($deviceGroup->type == 'dynamic') {
                /** @var DeviceGroup $deviceGroup */
                $deviceGroup->rules = $deviceGroup->getParser()->generateJoins()->toArray();
                $deviceGroup->save();
            }
        });
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}

if ($options['f'] === 'notify') {
    if (\LibreNMS\Config::has('alert.default_mail')) {
        send_mail(
            \LibreNMS\Config::get('alert.default_mail'),
            '[LibreNMS] Auto update has failed for ' . Config::get('distributed_poller_name'),
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

if ($options['f'] === 'recalculate_device_dependencies') {
    // fix broken dependency max_depth calculation in case things weren't done though eloquent

    try {
        if (Config::get('distributed_poller')) {
            MemcacheLock::lock('recalculate_device_dependencies', 0, 86000);
        }
        \LibreNMS\DB\Eloquent::boot();

        // update all root nodes and recurse, chunk so we don't blow up
        Device::doesntHave('parents')->with('children')->chunk(100, function (Collection $devices) {
            // anonymous recursive function
            $recurse = function (Device $device) use (&$recurse) {
                $device->updateMaxDepth();

                $device->children->each($recurse);
            };

            $devices->each($recurse);
        });
    } catch (LockException $e) {
        echo $e->getMessage() . PHP_EOL;
        exit(-1);
    }
}
