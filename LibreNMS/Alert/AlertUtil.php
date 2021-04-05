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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Alert;

use App\Models\Device;
use App\Models\User;
use DeviceCache;
use LibreNMS\Config;
use PHPMailer\PHPMailer\PHPMailer;

class AlertUtil
{
    /**
     * Get the rule_id for a specific alert
     *
     * @param int $alert_id
     * @return mixed|null
     */
    private static function getRuleId($alert_id)
    {
        $query = 'SELECT `rule_id` FROM `alerts` WHERE `id`=?';

        return dbFetchCell($query, [$alert_id]);
    }

    /**
     * Get the transport for a given alert_id
     *
     * @param int $alert_id
     * @return array
     */
    public static function getAlertTransports($alert_id)
    {
        $query = "SELECT b.transport_id, b.transport_type, b.transport_name FROM alert_transport_map AS a LEFT JOIN alert_transports AS b ON b.transport_id=a.transport_or_group_id WHERE a.target_type='single' AND a.rule_id=? UNION DISTINCT SELECT d.transport_id, d.transport_type, d.transport_name FROM alert_transport_map AS a LEFT JOIN alert_transport_groups AS b ON a.transport_or_group_id=b.transport_group_id LEFT JOIN transport_group_transport AS c ON b.transport_group_id=c.transport_group_id LEFT JOIN alert_transports AS d ON c.transport_id=d.transport_id WHERE a.target_type='group' AND a.rule_id=?";
        $rule_id = self::getRuleId($alert_id);

        return dbFetchRows($query, [$rule_id, $rule_id]);
    }

    /**
     * Returns the default transports
     *
     * @return array
     */
    public static function getDefaultAlertTransports()
    {
        $query = 'SELECT transport_id, transport_type, transport_name FROM alert_transports WHERE is_default=true';

        return dbFetchRows($query);
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
        $contacts = [];
        $uids = [];
        foreach ($results as $result) {
            $tmp = null;
            if (is_numeric($result['bill_id'])) {
                $tmpa = dbFetchRows('SELECT user_id FROM bill_perms WHERE bill_id = ?', [$result['bill_id']]);
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
            if (is_numeric($result['port_id'])) {
                $tmpa = dbFetchRows('SELECT user_id FROM ports_perms WHERE port_id = ?', [$result['port_id']]);
                foreach ($tmpa as $tmp) {
                    $uids[$tmp['user_id']] = $tmp['user_id'];
                }
            }
            if (is_numeric($result['device_id'])) {
                if (Config::get('alert.syscontact') == true) {
                    if (dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_bool' AND device_id = ?", [$result['device_id']])) {
                        $tmpa = dbFetchCell("SELECT attrib_value FROM devices_attribs WHERE attrib_type = 'override_sysContact_string' AND device_id = ?", [$result['device_id']]);
                    } else {
                        $tmpa = dbFetchCell('SELECT sysContact FROM devices WHERE device_id = ?', [$result['device_id']]);
                    }
                    if (! empty($tmpa)) {
                        $contacts[$tmpa] = '';
                    }
                }
                $tmpa = dbFetchRows('SELECT user_id FROM devices_perms WHERE device_id = ?', [$result['device_id']]);
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
            if (Config::get('alert.globals') && ($user['level'] >= 5 && $user['level'] < 10)) {
                $contacts[$user['email']] = $user['realname'];
            } elseif (Config::get('alert.admins') && $user['level'] == 10) {
                $contacts[$user['email']] = $user['realname'];
            } elseif (Config::get('alert.users') == true && in_array($user['user_id'], $uids)) {
                $contacts[$user['email']] = $user['realname'];
            }
        }

        $tmp_contacts = [];
        foreach ($contacts as $email => $name) {
            if (strstr($email, ',')) {
                $split_contacts = preg_split('/[,\s]+/', $email);
                foreach ($split_contacts as $split_email) {
                    if (! empty($split_email)) {
                        $tmp_contacts[$split_email] = $name;
                    }
                }
            } else {
                $tmp_contacts[$email] = $name;
            }
        }

        if (! empty($tmp_contacts)) {
            // Validate contacts so we can fall back to default if configured.
            $mail = new PHPMailer();
            foreach ($tmp_contacts as $tmp_email => $tmp_name) {
                if ($mail->validateAddress($tmp_email) != true) {
                    unset($tmp_contacts[$tmp_email]);
                }
            }
        }

        // Copy all email alerts to default contact if configured.
        $default_mail = Config::get('alert.default_mail');
        if (! isset($tmp_contacts[$default_mail]) && Config::get('alert.default_copy')) {
            $tmp_contacts[$default_mail] = '';
        }
        // Send email to default contact if no other contact found
        if (empty($tmp_contacts) && Config::get('alert.default_if_none') && $default_mail) {
            $tmp_contacts[$default_mail] = '';
        }

        return $tmp_contacts;
    }

    public static function getRules($device_id)
    {
        $query = 'SELECT DISTINCT a.* FROM alert_rules a
        LEFT JOIN alert_device_map d ON a.id=d.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND d.device_id = ?)
        LEFT JOIN alert_group_map g ON a.id=g.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND g.group_id IN (SELECT DISTINCT device_group_id FROM device_group_device WHERE device_id = ?))
        LEFT JOIN alert_location_map l ON a.id=l.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND l.location_id IN (SELECT DISTINCT location_id FROM devices WHERE device_id = ?))
        LEFT JOIN device_group_device dg ON g.group_id=dg.device_group_id AND dg.device_id = ?
        WHERE a.disabled = 0 AND (
            (d.device_id IS NULL AND g.group_id IS NULL)
            OR (a.invert_map = 0 AND (d.device_id=? OR dg.device_id=?))
            OR (a.invert_map = 1  AND (d.device_id != ? OR d.device_id IS NULL) AND (dg.device_id != ? OR dg.device_id IS NULL))
        )';

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

        return ! is_null($device) && $device->disable_notify;
    }

    /**
     * Process Macros
     * @param string $rule Rule to process
     * @param int $x Recursion-Anchor
     * @return string|bool
     */
    public static function runMacros($rule, $x = 1)
    {
        $macros = Config::get('alert.macros.rule', []);
        krsort($macros);
        foreach ($macros as $macro => $value) {
            if (! strstr($macro, ' ')) {
                $rule = str_replace('%macros.' . $macro, '(' . $value . ')', $rule);
            }
        }
        if (strstr($rule, '%macros.')) {
            if (++$x < 30) {
                $rule = self::runMacros($rule, $x);
            } else {
                return false;
            }
        }

        return $rule;
    }
}
