<?php
/**
 * AlertUtil.php
 *
 * Extending the built in logging to add an event logger function
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Alert;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Models\Device;
use App\Models\User;
use DeviceCache;
use LibreNMS\Config;
use PHPMailer\PHPMailer\PHPMailer;

class AlertUtil
{
    /**
     *
     * Get the rule_id for a specific alert
     *
     * @param $alert_id
     * @return mixed|null
     */

    private static function getRuleId($alert_id)
    {
        $query = "SELECT `rule_id` FROM `alerts` WHERE `id`=?";
        return dbFetchCell($query, [$alert_id]);
    }

    /**
     *
     * Get the transport for a given alert_id
     *
     * @param $alert_id
     * @return array
     */
    public static function getAlertTransports($alert_id, $device_id)
    {
        $query_mapto = "SELECT DISTINCT at.transport_id FROM alert_transports at
            LEFT JOIN transport_device_map d ON at.transport_id=d.transport_id AND (at.invert_map = 0 OR at.invert_map = 1 AND d.device_id = ?)
            LEFT JOIN transport_group_map g ON at.transport_id=g.transport_id AND (at.invert_map = 0 OR at.invert_map = 1 AND g.group_id IN (SELECT DISTINCT device_group_id FROM device_group_device WHERE device_id = ?))
            LEFT JOIN transport_location_map l ON at.transport_id=l.transport_id AND (at.invert_map = 0 OR at.invert_map = 1 AND l.location_id IN (SELECT DISTINCT location_id FROM devices WHERE device_id = ?))
            LEFT JOIN device_group_device dg ON g.group_id=dg.device_group_id AND dg.device_id = ?
            WHERE (
                (d.device_id IS NULL AND g.group_id IS NULL)
                OR (at.invert_map = 0 AND (d.device_id=? OR dg.device_id=?))
                OR (at.invert_map = 1  AND (d.device_id != ? OR d.device_id IS NULL) AND (dg.device_id != ? OR dg.device_id IS NULL))
            )";

        $local_now = CarbonImmutable::now(config('app.timezone'));
        $now = CarbonImmutable::now('UTC');

        $where_time = "(at.timerange = 0
        OR(at.timerange = 1 AND ((at.start_hr < at.end_hr AND at.start_hr <= ?
        AND at.end_hr >= ?) OR (at.start_hr > at.end_hr AND ((at.start_hr <= ?
        AND time((time(at.end_hr)+time(240000))) >= ?)
        OR (at.start_hr <= time((time(?)+time(240000)))
        AND time((time(at.end_hr)+time(240000))) >= time((time(?)+time(240000))))))
        AND (at.day LIKE ? OR at.day IS NULL))))";  # time(240000) : "24:00:00"

        $query = "SELECT at.transport_id, at.transport_type, at.transport_name
            FROM alert_transport_map AS atm
            LEFT JOIN alert_transports AS at ON at.transport_id=atm.transport_or_group_id
            WHERE atm.target_type='single' AND atm.rule_id=? AND at.transport_id IN (" . $query_mapto . ") AND " . $where_time . "
            UNION DISTINCT
            SELECT at.transport_id, at.transport_type, at.transport_name
            FROM alert_transport_map AS atm
            LEFT JOIN alert_transport_groups AS atg ON atm.transport_or_group_id=atg.transport_group_id
            LEFT JOIN transport_group_transport AS tgt ON atg.transport_group_id=tgt.transport_group_id
            LEFT JOIN alert_transports AS at ON tgt.transport_id=at.transport_id
            WHERE atm.target_type='group' AND atm.rule_id=? AND at.transport_id IN (" . $query_mapto . ") AND " . $where_time;

        $rule_id = self::getRuleId($alert_id);
        $params = [$rule_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id,
                   $now->toTimeString(), $now->toTimeString(), $now->toTimeString(), $now->toTimeString(),
                   $now->toTimeString(), $now->toTimeString(),
                   $local_now->format('%N%'),
                   $rule_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id,
                   $now->toTimeString(), $now->toTimeString(), $now->toTimeString(), $now->toTimeString(),
                   $now->toTimeString(), $now->toTimeString(),
                   $local_now->format('%N%')];
        return dbFetchRows($query, $params);
    }

    /**
     *
     * Returns the default transports
     *
     * @return array
     */
    public static function getDefaultAlertTransports($device_id)
    {
        $query_mapto = "SELECT DISTINCT at.transport_id FROM alert_transports at
            LEFT JOIN transport_device_map d ON at.transport_id=d.transport_id AND (at.invert_map = 0 OR at.invert_map = 1 AND d.device_id = ?)
            LEFT JOIN transport_group_map g ON at.transport_id=g.transport_id AND (at.invert_map = 0 OR at.invert_map = 1 AND g.group_id IN (SELECT DISTINCT device_group_id FROM device_group_device WHERE device_id = ?))
            LEFT JOIN transport_location_map l ON at.transport_id=l.transport_id AND (at.invert_map = 0 OR at.invert_map = 1 AND l.location_id IN (SELECT DISTINCT location_id FROM devices WHERE device_id = ?))
            LEFT JOIN device_group_device dg ON g.group_id=dg.device_group_id AND dg.device_id = ?
            WHERE (
                (d.device_id IS NULL AND g.group_id IS NULL)
                OR (at.invert_map = 0 AND (d.device_id=? OR dg.device_id=?))
                OR (at.invert_map = 1  AND (d.device_id != ? OR d.device_id IS NULL) AND (dg.device_id != ? OR dg.device_id IS NULL))
            )";

        $local_now = CarbonImmutable::now(config('app.timezone'));
        $now = CarbonImmutable::now('UTC');

        $where_time = "(at.timerange = 0
        OR(at.timerange = 1 AND ((at.start_hr < at.end_hr AND at.start_hr <= ?
        AND at.end_hr >= ?) OR (at.start_hr > at.end_hr AND ((at.start_hr <= ?
        AND time((time(at.end_hr)+time(240000))) >= ?)
        OR (at.start_hr <= time((time(?)+time(240000)))
        AND time((time(at.end_hr)+time(240000))) >= time((time(?)+time(240000))))))
        AND (at.day LIKE ? OR at.day IS NULL))))";  # time(240000) : "24:00:00"

        $query = "SELECT transport_id, transport_type, transport_name
            FROM alert_transports as at
            WHERE at.is_default=true AND at.transport_id IN (" . $query_mapto . ") AND " . $where_time;
        $params = [$device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id,
                   $now->toTimeString(), $now->toTimeString(), $now->toTimeString(), $now->toTimeString(),
                   $now->toTimeString(), $now->toTimeString(),
                   $local_now->format('%N%')];
        return dbFetchRows($query, $params);
    }

     /**
     * Find contacts for alert
     * @param array $results Rule-Result
     * @return array
     */
    public static function getContacts($results)
    {
        if (empty($results)) {
            return [];
        }
        if (Config::get('alert.default_only') === true || Config::get('alerts.email.default_only') === true) {
            $email = Config::get('alert.default_mail', Config::get('alerts.email.default'));
            return $email ? [$email => ''] : [];
        }
        $users = User::query()->thisAuth()->get();
        $contacts = array();
        $uids = array();
        foreach ($results as $result) {
            $tmp  = null;
            if (is_numeric($result["bill_id"])) {
                $tmpa = dbFetchRows("SELECT user_id FROM bill_perms WHERE bill_id = ?", array($result["bill_id"]));
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
            if (is_numeric($result["port_id"])) {
                $tmpa = dbFetchRows("SELECT user_id FROM ports_perms WHERE port_id = ?", array($result["port_id"]));
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
            if (is_numeric($result["device_id"])) {
                if (Config::get('alert.syscontact') == true) {
                    if (dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_bool' AND device_id = ?", [$result["device_id"]])) {
                        $tmpa = dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_string' AND device_id = ?", array($result["device_id"]));
                    } else {
                        $tmpa = dbFetchCell("SELECT sysContact FROM devices WHERE device_id = ?", array($result["device_id"]));
                    }
                    if (!empty($tmpa)) {
                        $contacts[$tmpa] = '';
                    }
                }
                $tmpa = dbFetchRows("SELECT user_id FROM devices_perms WHERE device_id = ?", array($result["device_id"]));
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
        }
        foreach ($users as $user) {
            if (empty($user['email'])) {
                continue; // no email, skip this user
            }
            if (empty($user['realname'])) {
                $user['realname'] = $user['username'];
            }
            if (Config::get('alert.globals') && ( $user['level'] >= 5 && $user['level'] < 10 )) {
                            $contacts[$user['email']] = $user['realname'];
            } elseif (Config::get('alert.admins') && $user['level'] == 10) {
                $contacts[$user['email']] = $user['realname'];
            } elseif (Config::get('alert.users') == true && in_array($user['user_id'], $uids)) {
                $contacts[$user['email']] = $user['realname'];
            }
        }

        $tmp_contacts = array();
        foreach ($contacts as $email => $name) {
            if (strstr($email, ',')) {
                $split_contacts = preg_split('/[,\s]+/', $email);
                foreach ($split_contacts as $split_email) {
                    if (!empty($split_email)) {
                        $tmp_contacts[$split_email] = $name;
                    }
                }
            } else {
                $tmp_contacts[$email] = $name;
            }
        }

        if (!empty($tmp_contacts)) {
            // Validate contacts so we can fall back to default if configured.
            $mail = new PHPMailer();
            foreach ($tmp_contacts as $tmp_email => $tmp_name) {
                if ($mail->validateAddress($tmp_email) != true) {
                    unset($tmp_contacts[$tmp_email]);
                }
            }
        }

        # Copy all email alerts to default contact if configured.
        $default_mail = Config::get('alert.default_mail');
        if (!isset($tmp_contacts[$default_mail]) && Config::get('alert.default_copy')) {
            $tmp_contacts[$default_mail] = '';
        }
        # Send email to default contact if no other contact found
        if (empty($tmp_contacts) && Config::get('alert.default_if_none') && $default_mail) {
            $tmp_contacts[$default_mail] = '';
        }

        return $tmp_contacts;
    }

    public static function getRules($device_id)
    {
        $query = "SELECT DISTINCT a.* FROM alert_rules a
        LEFT JOIN alert_device_map d ON a.id=d.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND d.device_id = ?)
        LEFT JOIN alert_group_map g ON a.id=g.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND g.group_id IN (SELECT DISTINCT device_group_id FROM device_group_device WHERE device_id = ?))
        LEFT JOIN alert_location_map l ON a.id=l.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND l.location_id IN (SELECT DISTINCT location_id FROM devices WHERE device_id = ?))
        LEFT JOIN device_group_device dg ON g.group_id=dg.device_group_id AND dg.device_id = ?
        WHERE a.disabled = 0 AND (
            (d.device_id IS NULL AND g.group_id IS NULL)
            OR (a.invert_map = 0 AND (d.device_id=? OR dg.device_id=?))
            OR (a.invert_map = 1  AND (d.device_id != ? OR d.device_id IS NULL) AND (dg.device_id != ? OR dg.device_id IS NULL))
        )";

        $params = [$device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id];
        return dbFetchRows($query, $params);
    }

    /**
     * Check if device is under maintenance
     * @param int $device_id Device-ID
     * @return bool
     */
    public static function isMaintenance($device_id)
    {
        return DeviceCache::get($device_id)->isUnderMaintenance();
    }

    /**
     * Check if device is set to ignore alerts
     * @param int $device_id Device-ID
     * @return bool
     */
    public static function hasDisableNotify($device_id)
    {
        $device = Device::find($device_id);
        return !is_null($device) && $device->disable_notify;
    }

    /**
     * Process Macros
     * @param string $rule Rule to process
     * @param int $x Recursion-Anchor
     * @return string|boolean
     */
    public static function runMacros($rule, $x = 1)
    {
        $macros = Config::get('alert.macros.rule', []) .
        krsort($macros);
        foreach ($macros as $macro => $value) {
            if (!strstr($macro, " ")) {
                $rule = str_replace('%macros.'.$macro, '('.$value.')', $rule);
            }
        }
        if (strstr($rule, "%macros.")) {
            if (++$x < 30) {
                $rule = self::runMacros($rule, $x);
            } else {
                return false;
            }
        }
        return $rule;
    }
}
