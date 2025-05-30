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
 *
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Alert;

use App\Facades\LibrenmsConfig;
use App\Models\Alert;
use App\Models\AlertTransport;
use App\Models\AlertTransportMap;
use App\Models\Device;
use App\Models\User;
use DB;
use DeviceCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use PHPMailer\PHPMailer\PHPMailer;

class AlertUtil
{
    /**
     * Get the transport for a given alert_id
     *
     * @param  int  $alert_id
     * @return array
     */
    public static function getAlertTransports($alert_id)
    {
        $first = AlertTransportMap::leftJoin('alert_transport_groups as b', 'alert_transport_map.transport_or_group_id', '=', 'b.transport_group_id')
        ->leftJoin('transport_group_transport as c', 'c.transport_group_id', '=', 'b.transport_group_id')
            ->leftJoin('alert_transports as d', 'c.transport_id', '=', 'd.transport_id')
            ->where('alert_transport_map.rule_id', Alert::find($alert_id)->rule_id ?? null)
            ->where('alert_transport_map.target_type', 'group')
            ->select('d.transport_id', 'd.transport_type', 'd.transport_name');

        return AlertTransportMap::leftJoin('alert_transports as b', 'b.transport_id', '=', 'alert_transport_map.transport_or_group_id')
            ->where('alert_transport_map.rule_id', Alert::find($alert_id)->rule_id ?? null)
            ->where('alert_transport_map.target_type', 'single')
            ->select('b.transport_id', 'b.transport_type', 'b.transport_name')
            ->union($first)
            ->distinct()
            ->get()
            ->toArray();
    }

    /**
     * Returns the default transports
     *
     * @return array
     */
    public static function getDefaultAlertTransports()
    {
        return AlertTransport::where('is_default', true)
            ->select('transport_id', 'transport_type', 'transport_name')
            ->get()
            ->toArray();
    }

    /**
     * Find contacts for alert
     *
     * @param  array  $results  Rule-Result
     * @return array
     */
    public static function getContacts($results)
    {
        if (empty($results)) {
            return [];
        }

        if (LibrenmsConfig::get('alert.default_only') === true || LibrenmsConfig::get('alerts.email.default_only') === true) {
            $email = LibrenmsConfig::get('alert.default_mail', LibrenmsConfig::get('alerts.email.default'));

            return $email ? [$email => ''] : [];
        }

        $contacts = [];

        if (LibrenmsConfig::get('alert.syscontact')) {
            $contacts = array_merge($contacts, self::findContactsSysContact($results));
        }

        if (LibrenmsConfig::get('alert.users')) {
            $contacts = array_merge($contacts, self::findContactsOwners($results));
        }

        $roles = LibrenmsConfig::get('alert.globals')
            ? ['admin', 'global-read']
            : (LibrenmsConfig::get('alert.admins') ? ['admin'] : []);
        if ($roles) {
            $contacts = array_merge($contacts, self::findContactsRoles($roles));
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
        $default_mail = LibrenmsConfig::get('alert.default_mail');
        if (! isset($tmp_contacts[$default_mail]) && LibrenmsConfig::get('alert.default_copy')) {
            $tmp_contacts[$default_mail] = '';
        }
        // Send email to default contact if no other contact found
        if (empty($tmp_contacts) && LibrenmsConfig::get('alert.default_if_none') && $default_mail) {
            $tmp_contacts[$default_mail] = '';
        }

        return $tmp_contacts;
    }

    public static function findContactsRoles(array $roles): array
    {
        return User::role($roles)->whereNot('email', '')->pluck('realname', 'email')->toArray();
    }

    public static function findContactsSysContact(array $results): array
    {
        $contacts = [];

        foreach ($results as $result) {
            $device = DeviceCache::get($result['device_id']);
            $email = $device->getAttrib('override_sysContact_bool')
                ? $device->getAttrib('override_sysContact_string')
                : $device->sysContact;
            $contacts[$email] = '';
        }

        return $contacts;
    }

    public static function findContactsOwners(array $results): array
    {
        return User::whereNot('email', '')->where(function (Builder $query) use ($results) {
            if ($device_ids = array_filter(Arr::pluck($results, 'device_id'))) {
                $query->orWhereHas('devicesOwned', fn ($q) => $q->whereIn('devices_perms.device_id', $device_ids));
            }
            if ($port_ids = array_filter(Arr::pluck($results, 'port_id'))) {
                $query->orWhereHas('portsOwned', fn ($q) => $q->whereIn('ports_perms.port_id', $port_ids));
            }
            if ($bill_ids = array_filter(Arr::pluck($results, 'bill_id'))) {
                $query->orWhereHas('bills', fn ($q) => $q->whereIn('bill_perms.bill_id', $bill_ids));
            }
        })->pluck('realname', 'email')->all();
    }

    public static function getRules($device_id)
    {
        $query = 'SELECT DISTINCT a.* FROM alert_rules a
        LEFT JOIN alert_device_map d ON a.id=d.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND d.device_id = ?)
        LEFT JOIN alert_group_map g ON a.id=g.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND g.group_id IN (SELECT DISTINCT device_group_id FROM device_group_device WHERE device_id = ?))
        LEFT JOIN alert_location_map l ON a.id=l.rule_id AND (a.invert_map = 0 OR a.invert_map = 1 AND l.location_id IN (SELECT DISTINCT location_id FROM devices WHERE device_id = ?))
        LEFT JOIN devices ld ON l.location_id=ld.location_id AND ld.device_id = ?
        LEFT JOIN device_group_device dg ON g.group_id=dg.device_group_id AND dg.device_id = ?
        WHERE a.disabled = 0 AND (
            (d.device_id IS NULL AND g.group_id IS NULL AND l.location_id IS NULL)
            OR (a.invert_map = 0 AND (d.device_id=? OR dg.device_id=? OR ld.device_id=?))
            OR (a.invert_map = 1  AND (d.device_id != ? OR d.device_id IS NULL) AND (dg.device_id != ? OR dg.device_id IS NULL) AND (ld.device_id != ? OR ld.device_id IS NULL))
        )';
        $params = [$device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id, $device_id];

        return array_map(fn ($item) => (array) $item, DB::select($query, $params));
    }

    /**
     * Check if device is under maintenance
     *
     * @param  int  $device_id  Device-ID
     * @return bool
     */
    public static function isMaintenance($device_id)
    {
        return DeviceCache::get($device_id)->isUnderMaintenance();
    }

    /**
     * Check if device is set to ignore alerts
     *
     * @param  int  $device_id  Device-ID
     * @return bool
     */
    public static function hasDisableNotify($device_id)
    {
        $device = Device::find($device_id);

        return ! is_null($device) && $device->disable_notify;
    }

    /**
     * Process Macros
     *
     * @param  string  $rule  Rule to process
     * @param  int  $x  Recursion-Anchor
     * @return string|bool
     */
    public static function runMacros($rule, $x = 1)
    {
        $macros = LibrenmsConfig::get('alert.macros.rule', []);
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
