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
use LibreNMS\Util\Debug;
use LibreNMS\Validations\Php;

$init_modules = ['alerts'];
require __DIR__ . '/includes/init.php';
include_once __DIR__ . '/includes/notifications.php';

$options = getopt('df:o:t:r:');

if (isset($options['d'])) {
    echo "DEBUG\n";
    Debug::set();
}

if ($options['f'] === 'update') {
    if (! Config::get('update')) {
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
    $lock = Cache::lock('rrd_purge', 86000);
    if ($lock->get()) {
        $rrd_purge = Config::get('rrd_purge');
        $rrd_dir = Config::get('rrd_dir');

        if (is_numeric($rrd_purge) && $rrd_purge > 0) {
            $cmd = "find $rrd_dir -type f -mtime +$rrd_purge -print -exec rm -f {} +";
            $purge = `$cmd`;
            if (! empty($purge)) {
                echo "Purged the following RRD files due to old age (over $rrd_purge days old):\n";
                echo $purge;
            }
        }
        $lock->release();
    }
}

if ($options['f'] === 'syslog') {
    $lock = Cache::lock('syslog_purge', 86000);
    if ($lock->get()) {
        $syslog_purge = Config::get('syslog_purge');

        if (is_numeric($syslog_purge)) {
            $rows = (int) dbFetchCell('SELECT MIN(seq) FROM syslog');
            $initial_rows = $rows;
            while (true) {
                $limit = dbFetchCell('SELECT seq FROM syslog WHERE seq >= ? ORDER BY seq LIMIT 1000,1', [$rows]);
                if (empty($limit)) {
                    break;
                }

                // Deletes are done in blocks of 1000 to avoid a single very large operation.
                if (dbDelete('syslog', 'seq >= ? AND seq < ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)', [$rows, $limit, $syslog_purge]) > 0) {
                    $rows = $limit;
                } else {
                    break;
                }
            }

            dbDelete('syslog', 'seq >= ? AND timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)', [$rows, $syslog_purge]);
            $final_rows = $rows - $initial_rows;
            echo "Syslog cleared for entries over $syslog_purge days (about $final_rows rows)\n";
        }
        $lock->release();
    }
}

if ($options['f'] === 'ports_fdb') {
    $ret = lock_and_purge('ports_fdb', 'updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
    exit($ret);
}
if ($options['f'] === 'route') {
    $ret = lock_and_purge('route', 'updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
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

if ($options['f'] === 'callback') {
    include_once 'includes/callback.php';
}

if ($options['f'] === 'device_perf') {
    $ret = lock_and_purge('device_perf', 'timestamp < DATE_SUB(NOW(),INTERVAL ? DAY)');
    exit($ret);
}

if ($options['f'] === 'ports_purge') {
    if (Config::get('ports_purge')) {
        $lock = Cache::lock('syslog_purge', 86000);
        if ($lock->get()) {
            \App\Models\Port::query()->with(['device' => function ($query) {
                $query->select('device_id', 'hostname');
            }])->isDeleted()->chunk(100, function ($ports) {
                foreach ($ports as $port) {
                    $port->delete();
                }
            });
            echo "All deleted ports now purged\n";
            $lock->release();
        }
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

        // if update is not set to false and version is min or newer
        if (Config::get('update') && $options['r']) {
            if ($options['r'] === 'php53') {
                $phpver = '5.6.4';
                $eol_date = 'January 10th, 2018';
            } elseif ($options['r'] === 'php56' || $options['r'] === 'php71' || $options['r'] === 'php72') {
                $phpver = Php::PHP_MIN_VERSION;
                $eol_date = Php::PHP_MIN_VERSION_DATE;
            }
            if (isset($phpver)) {
                new_notification(
                    $error_title,
                    "PHP version $phpver is the minimum supported version as of $eol_date.  We recommend you update to PHP a supported version of PHP (" . Php::PHP_RECOMMENDED_VERSION . ' suggested) to continue to receive updates.  If you do not update PHP, LibreNMS will continue to function but stop receiving bug fixes and updates.',
                    2,
                    'daily.sh'
                );
                exit(1);
            }
        }

        remove_notification($error_title);
        exit(0);
    } elseif ($options['t'] === 'pythonver') {
        $error_title = 'Error: Python requirements not met';

        // if update is not set to false and version is min or newer
        if (Config::get('update') && $options['r']) {
            if ($options['r'] === 'python3-missing') {
                new_notification(
                    $error_title,
                    'Python 3 is required to run LibreNMS as of May, 2020. You need to install Python 3 to continue to receive updates.  If you do not install Python 3 and required packages, LibreNMS will continue to function but stop receiving bug fixes and updates.',
                    2,
                    'daily.sh'
                );
                exit(1);
            } elseif ($options['r'] === 'python3-deps') {
                new_notification(
                    $error_title,
                    'Python 3 dependencies are missing. You need to install them via pip3 install -r requirements.txt or system packages to continue to receive updates.  If you do not install Python 3 and required packages, LibreNMS will continue to function but stop receiving bug fixes and updates.',
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
    $lock = Cache::lock('notifications', 86000);
    if ($lock->get()) {
        post_notifications();
        $lock->release();
    }
}

if ($options['f'] === 'bill_data') {
    // Deletes data older than XX months before the start of the last complete billing period
    $msg = "Deleting billing data more than %d month before the last completed billing cycle\n";
    $table = 'bill_data';
    $sql = 'DELETE bill_data
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
            ON bill_data.bill_id = q.bill_id AND bill_data.timestamp < q.threshold;';
    lock_and_purge_query($table, $sql, $msg);
}

if ($options['f'] === 'alert_log') {
    $msg = "Deleting alert_logs more than %d days that are not active\n";
    $table = 'alert_log';
    $sql = 'DELETE alert_log
                FROM alert_log
                INNER JOIN alerts
                ON alerts.device_id=alert_log.device_id AND alerts.rule_id=alert_log.rule_id
                WHERE alerts.state=0 AND alert_log.time_logged < DATE_SUB(NOW(),INTERVAL ? DAY)
                ';
    lock_and_purge_query($table, $sql, $msg);

    // alert_log older than $config['alert_log_purge'] days match now only the alert_log of active alerts
    // in case of flapping of an alert, many entries are kept in alert_log
    // we want only to keep the last alert_log that contains the alert details

    $msg = "Deleting history of active alert_logs more than %d days\n";
    $sql = 'DELETE
                    FROM alert_log
                    WHERE id IN(
                        SELECT id FROM(
                            SELECT id
                            FROM alert_log a1
                            WHERE
                                time_logged < DATE_SUB(NOW(),INTERVAL ? DAY)
                                AND (device_id, rule_id, time_logged) NOT IN (
                                    SELECT device_id, rule_id, max(time_logged)
                                    FROM alert_log a2 WHERE a1.device_id = a2.device_id AND a1.rule_id = a2.rule_id
                                    AND a2.time_logged < DATE_SUB(NOW(),INTERVAL ? DAY)
                                )
                        ) as c
                    )
                ';
    $purge_duration = Config::get('alert_log_purge');
    if (! (is_numeric($purge_duration) && $purge_duration > 0)) {
        return -2;
    }
    $sql = str_replace('?', strval($purge_duration), $sql);
    lock_and_purge_query($table, $sql, $msg);
}

if ($options['f'] === 'purgeusers') {
    $lock = Cache::lock('purgeusers', 86000);
    if ($lock->get()) {
        $purge = 0;
        if (is_numeric(\LibreNMS\Config::get('radius.users_purge')) && Config::get('auth_mechanism') === 'radius') {
            $purge = \LibreNMS\Config::get('radius.users_purge');
        }
        if (is_numeric(\LibreNMS\Config::get('active_directory.users_purge')) && Config::get('auth_mechanism') === 'active_directory') {
            $purge = \LibreNMS\Config::get('active_directory.users_purge');
        }
        if ($purge > 0) {
            $users = \App\Models\AuthLog::where('datetime', '>=', \Carbon\Carbon::now()->subDays($purge))
                ->distinct()->pluck('user')
                ->merge(\App\Models\User::has('apiToken')->pluck('username')) // don't purge users with api tokens
                ->unique();

            if (\App\Models\User::thisAuth()->whereNotIn('username', $users)->delete()) {
                echo "Removed users that haven't logged in for $purge days\n";
            }
        }
        $lock->release();
    }
}

if ($options['f'] === 'refresh_alert_rules') {
    $lock = Cache::lock('refresh_alert_rules', 86000);
    if ($lock->get()) {
        echo 'Refreshing alert rules queries' . PHP_EOL;
        $rules = dbFetchRows('SELECT `id`, `rule`, `builder`, `extra` FROM `alert_rules`');
        foreach ($rules as $rule) {
            $rule_options = json_decode($rule['extra'], true);
            if ($rule_options['options']['override_query'] !== 'on') {
                $data['query'] = AlertDB::genSQL($rule['rule'], $rule['builder']);
                if (! empty($data['query'])) {
                    dbUpdate($data, 'alert_rules', 'id=?', [$rule['id']]);
                    unset($data);
                }
            }
        }
        $lock->release();
    }
}

if ($options['f'] === 'refresh_device_groups') {
    $lock = Cache::lock('refresh_device_groups', 86000);
    if ($lock->get()) {
        echo 'Refreshing device group table relationships' . PHP_EOL;
        DeviceGroup::all()->each(function ($deviceGroup) {
            if ($deviceGroup->type == 'dynamic') {
                /** @var DeviceGroup $deviceGroup */
                $deviceGroup->rules = $deviceGroup->getParser()->generateJoins()->toArray();
                $deviceGroup->save();
            }
        });
        $lock->release();
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
    $lock = Cache::lock('peeringdb', 86000);
    if ($lock->get()) {
        cache_peeringdb();
        $lock->release();
    }
}

if ($options['f'] === 'mac_oui') {
    $lock = Cache::lock('macouidb', 86000);
    if ($lock->get()) {
        $res = cache_mac_oui();
        $lock->release();
        exit($res);
    }
}

if ($options['f'] === 'refresh_os_cache') {
    echo 'Clearing OS cache' . PHP_EOL;
    unlink(Config::get('install_dir') . '/cache/os_defs.cache');
}

if ($options['f'] === 'recalculate_device_dependencies') {
    // fix broken dependency max_depth calculation in case things weren't done though eloquent

    $lock = Cache::lock('recalculate_device_dependencies', 86000);
    if ($lock->get()) {
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
        $lock->release();
    }
}
