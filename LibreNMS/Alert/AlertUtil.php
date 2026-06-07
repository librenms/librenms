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
use App\Models\AlertOperationSegment;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\User;
use DeviceCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\AlertRuleOperationPhase;
use LibreNMS\Enum\MaintenanceStatus;
use PHPMailer\PHPMailer\PHPMailer;

class AlertUtil
{
    /**
     * Get the rule_id for a specific alert
     *
     * @param  int  $alert_id
     * @return mixed|null
     */
    private static function getRuleId($alert_id)
    {
        return Alert::find($alert_id)?->rule_id;
    }

    /**
     * Map the alert state to the operation phase
     *
     * @param  int  $state  The alert state
     * @return string The operation phase
     */
    public static function mapAlertStateToOperationPhase(int $state): string
    {
        // UI-defined operations currently use problem phase only.
        // Keep all states mapped to problem until dedicated phase config is reintroduced.
        unset($state);

        return AlertRuleOperationPhase::PROBLEM;
    }

    /**
     * True when the rule has at least one operation row (notifications are configured at the operation level).
     */
    public static function ruleHasAlertOperations(int $ruleId): bool
    {
        return AlertRule::find($ruleId)?->alert_operation_id !== null;
    }

    public static function operationNotificationsSuppressed(int $ruleId): bool
    {
        $rule = AlertRule::query()
            ->with('alertOperation:id,notifications_suppressed')
            ->whereKey($ruleId)
            ->first(['id', 'alert_operation_id']);

        if ($rule === null || $rule->alert_operation_id === null) {
            return true;
        }

        return $rule->alertOperation === null || (bool) $rule->alertOperation->notifications_suppressed;
    }

    /**
     * Independent per-segment scheduler for the problem phase.
     *
     * Each segment runs its own timer measured from when the alert started (anchor):
     *  - the first notification is due after `start_in_seconds`
     *  - subsequent notifications repeat every `step_duration_seconds`
     *  - `escalation_step_from`/`escalation_step_to` cap how many times the segment fires
     *    (to = null means it fires for as long as the alert is active)
     *
     * @param  array<string, mixed>  $details  alert_log details (decoded, mutated in place)
     * @return array<int, int>  segment ids due to fire this cycle
     */
    public static function dueProblemSegments(int $ruleId, array &$details): array
    {
        $rule = AlertRule::query()
            ->with('alertOperation:id,default_operation_step_duration_seconds')
            ->whereKey($ruleId)
            ->first(['id', 'alert_operation_id']);

        if ($rule === null || $rule->alert_operation_id === null) {
            return [];
        }

        $segments = AlertOperationSegment::where('alert_operation_id', $rule->alert_operation_id)
            ->where('operation_phase', AlertRuleOperationPhase::PROBLEM)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        if ($segments->isEmpty()) {
            return [];
        }

        $opDefault = $rule->alertOperation?->default_operation_step_duration_seconds;
        if ($opDefault === null) {
            $opDefault = max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_step_duration', LibrenmsConfig::get('alert_rule.interval')));
        }
        $defaultStep = max(0, (int) $opDefault);

        $now = time();
        $tolerance = (int) LibrenmsConfig::get('alert.tolerance_window');

        $anchor = (int) ($details['op_anchor'] ?? 0);
        if ($anchor <= 0) {
            $anchor = $now;
            $details['op_anchor'] = $anchor;
        }

        if (! isset($details['op_seg']) || ! is_array($details['op_seg'])) {
            $details['op_seg'] = [];
        }

        $segmentData = $segments->map(static fn (AlertOperationSegment $segment) => [
            'id' => (int) $segment->id,
            'escalation_step_from' => (int) $segment->escalation_step_from,
            'escalation_step_to' => $segment->escalation_step_to === null ? null : (int) $segment->escalation_step_to,
            'start_in_seconds' => (int) $segment->start_in_seconds,
            'step_duration_seconds' => (int) $segment->step_duration_seconds,
        ])->all();

        [$due, $details['op_seg']] = self::evaluateSegmentTimers($segmentData, $details['op_seg'], $anchor, $now, $tolerance, $defaultStep);

        return $due;
    }

    /**
     * For each segment, the first notification is due at `anchor + start_in_seconds`, then it repeats
     * every `step_duration_seconds` measured from its previous fire (so a paused poller catches up one
     * notification per cycle rather than bursting). `escalation_step_from`/`escalation_step_to` only cap
     * how many times a segment may fire (to = null means unlimited); they do not affect the timing.
     *
     * @param  array<int, array{id:int, escalation_step_from:int, escalation_step_to:int|null, start_in_seconds:int, step_duration_seconds:int}>  $segments
     * @param  array<string, array{fires:int, last:int}>  $state  per-segment timer state keyed by segment id
     * @return array{0: array<int, int>, 1: array<string, array{fires:int, last:int}>}  [due segment ids, updated state]
     */
    public static function evaluateSegmentTimers(array $segments, array $state, int $anchor, int $now, int $tolerance, int $defaultStep): array
    {
        $due = [];
        foreach ($segments as $segment) {
            $from = max(1, (int) $segment['escalation_step_from']);
            $to = $segment['escalation_step_to'] === null ? null : (int) $segment['escalation_step_to'];
            $maxFires = $to === null ? PHP_INT_MAX : max(0, $to - $from + 1);
            if ($maxFires <= 0) {
                continue;
            }

            $startIn = max(0, (int) $segment['start_in_seconds']);
            $stepDuration = (int) $segment['step_duration_seconds'];
            $stepDuration = $stepDuration > 0 ? $stepDuration : $defaultStep;

            $key = (string) $segment['id'];
            $segmentState = $state[$key] ?? ['fires' => 0, 'last' => 0];
            $fires = (int) ($segmentState['fires'] ?? 0);
            $last = (int) ($segmentState['last'] ?? 0);
            if ($fires >= $maxFires) {
                continue;
            }

            // First fire is anchored to the alert start; later fires repeat from the previous
            // fire so a late/paused poller catches up one notification per cycle instead of bursting.
            $dueAt = $fires === 0 ? ($anchor + $startIn) : ($last + max(1, $stepDuration));
            if (($now + $tolerance) >= $dueAt) {
                $due[] = (int) $segment['id'];
                $state[$key] = ['fires' => $fires + 1, 'last' => $now];
            }
        }

        return [$due, $state];
    }

    /**
     * Resolve the (deduplicated) transports for a set of operation segments, expanding groups.
     *
     * @param  array<int, int>  $segmentIds
     * @return array<int, array<string, mixed>>
     */
    public static function segmentTransports(array $segmentIds): array
    {
        $segmentIds = array_values(array_unique(array_map('intval', $segmentIds)));
        if (empty($segmentIds)) {
            return [];
        }

        $single = DB::table('alert_operation_transport_map as m')
            ->join('alert_transports as b', 'b.transport_id', '=', 'm.transport_or_group_id')
            ->where('m.target_type', '=', 'single')
            ->whereIn('m.segment_id', $segmentIds)
            ->select(['b.transport_id', 'b.transport_type', 'b.transport_name'])
            ->distinct()
            ->get();

        $group = DB::table('alert_operation_transport_map as m')
            ->join('alert_transport_groups as g', 'g.transport_group_id', '=', 'm.transport_or_group_id')
            ->join('transport_group_transport as c', 'c.transport_group_id', '=', 'g.transport_group_id')
            ->join('alert_transports as d', 'd.transport_id', '=', 'c.transport_id')
            ->where('m.target_type', '=', 'group')
            ->whereIn('m.segment_id', $segmentIds)
            ->select(['d.transport_id', 'd.transport_type', 'd.transport_name'])
            ->distinct()
            ->get();

        return $single
            ->concat($group)
            ->unique('transport_id')
            ->values()
            ->map(static fn ($row) => [
                'transport_id' => (int) $row->transport_id,
                'transport_type' => (string) $row->transport_type,
                'transport_name' => (string) $row->transport_name,
            ])
            ->all();
    }

    /**
     * Get transports for a given alert (per operation phase and escalation step).
     *
     * @param  int  $alert_id
     * @param  string|null  $operation_phase  problem|recovery|update; inferred from alerts.state if null
     * @param  int  $escalation_step  1-based escalation step for problem operations
     * @return array<int, array<string, mixed>>
     */
    public static function getAlertTransports($alert_id, ?string $operation_phase = null, int $escalation_step = 1)
    {
        $rule_id = self::getRuleId($alert_id);
        if ($rule_id === null) {
            return [];
        }

        if ($operation_phase === null) {
            // Use Eloquent instead of raw SQL for the alert state lookup.
            $state = (int) (Alert::query()->where('id', $alert_id)->value('state') ?? 0);
            $operation_phase = self::mapAlertStateToOperationPhase($state);
        }

        if (! self::ruleHasAlertOperations((int) $rule_id)) {
            return [];
        }

        $rule = AlertRule::query()->whereKey($rule_id)->first(['alert_operation_id']);
        if ($rule === null || $rule->alert_operation_id === null) {
            return [];
        }

        $operationId = $rule->alert_operation_id;
        // Prefer the alert's phase; if no segments exist for recovery/update, fall back to problem
        // (UI-defined operations store segments as problem-only).
        $phasesToTry = $operation_phase === AlertRuleOperationPhase::PROBLEM
            ? [AlertRuleOperationPhase::PROBLEM]
            : [$operation_phase, AlertRuleOperationPhase::PROBLEM];

        $single = collect();
        $group = collect();
        foreach ($phasesToTry as $phase) {
            // “Single” transports mapped per segment.
            $single = DB::table('alert_operation_segments')
                ->join('alert_operation_transport_map as m', 'm.segment_id', '=', 'alert_operation_segments.id')
                ->join('alert_transports as b', 'b.transport_id', '=', 'm.transport_or_group_id')
                ->where('m.target_type', '=', 'single')
                ->where('alert_operation_segments.alert_operation_id', '=', $operationId)
                ->where('alert_operation_segments.operation_phase', '=', $phase)
                ->where('alert_operation_segments.escalation_step_from', '<=', $escalation_step)
                ->where(function ($q) use ($escalation_step): void {
                    $q->whereNull('alert_operation_segments.escalation_step_to')
                        ->orWhereRaw('? <= alert_operation_segments.escalation_step_to', [$escalation_step]);
                })
                ->select(['b.transport_id', 'b.transport_type', 'b.transport_name'])
                ->distinct()
                ->get();

            // Transport groups: expand group membership via transport_group_transport.
            $group = DB::table('alert_operation_segments')
                ->join('alert_operation_transport_map as m', 'm.segment_id', '=', 'alert_operation_segments.id')
                ->join('alert_transport_groups as g', 'g.transport_group_id', '=', 'm.transport_or_group_id')
                ->join('transport_group_transport as c', 'c.transport_group_id', '=', 'g.transport_group_id')
                ->join('alert_transports as d', 'd.transport_id', '=', 'c.transport_id')
                ->where('m.target_type', '=', 'group')
                ->where('alert_operation_segments.alert_operation_id', '=', $operationId)
                ->where('alert_operation_segments.operation_phase', '=', $phase)
                ->where('alert_operation_segments.escalation_step_from', '<=', $escalation_step)
                ->where(function ($q) use ($escalation_step): void {
                    $q->whereNull('alert_operation_segments.escalation_step_to')
                        ->orWhereRaw('? <= alert_operation_segments.escalation_step_to', [$escalation_step]);
                })
                ->select(['d.transport_id', 'd.transport_type', 'd.transport_name'])
                ->distinct()
                ->get();

            if ($single->isNotEmpty() || $group->isNotEmpty()) {
                break;
            }
        }

        return $single
            ->concat($group)
            // Keep result stable and prevent duplicates when the same transport appears via both mappings.
            ->unique('transport_id')
            ->values()
            ->map(static fn ($row) => [
                'transport_id' => (int) $row->transport_id,
                'transport_type' => (string) $row->transport_type,
                'transport_name' => (string) $row->transport_name,
            ])
            ->all();
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
            if (strstr((string) $email, ',')) {
                $split_contacts = preg_split('/[,\s]+/', (string) $email);
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
        return User::whereNot('email', '')->where(function (Builder $query) use ($results): void {
            if ($device_ids = array_filter(Arr::pluck($results, 'device_id'))) {
                $query->orWhereHas('devicesOwned', fn ($q) => $q->whereIn('devices_perms.device_id', $device_ids));
                // Find all device groups that users have been granted access to where the device group also contains at least one device that we are looking for
                $query->orWhereHas('deviceGroups', fn ($q) => $q->whereIn('device_groups.id', DeviceGroup::WhereHas('devices', fn ($dq) => $dq->whereIn('devices.device_id', $device_ids))->pluck('device_groups.id')));
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

        return dbFetchRows($query, $params);
    }

    /**
     * Check if device is under maintenance
     *
     * @param  int  $device_id  Device-ID
     * @return MaintenanceStatus
     */
    public static function getMaintenanceStatus($device_id): MaintenanceStatus
    {
        return DeviceCache::get($device_id)->getMaintenanceStatus();
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
            if (! strstr((string) $macro, ' ')) {
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
