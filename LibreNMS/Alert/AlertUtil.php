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
use App\Models\BgpPeer;
use App\Models\DeviceGroup;
use App\Models\Mempool;
use App\Models\Processor;
use App\Models\Sensor;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Database\Eloquent\Model;
use DeviceCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\AlertRuleOperationPhase;
use PHPMailer\PHPMailer\PHPMailer;

class AlertUtil
{
    /**
     * Map of alert-query id columns to registered morph aliases (see AppServiceProvider).
     * Used to resolve the entity a problem is about (Port, Sensor, ...).
     */
    private const ENTITY_COLUMN_MAP = [
        'port_id' => 'interface',
        'sensor_id' => 'sensor',
        'bgpPeer_id' => 'bgppeer',
        'service_id' => 'service',
        'mempool_id' => 'mempool',
        'processor_id' => 'processor',
        'storage_id' => 'storage',
        'app_id' => 'application',
        'accesspoint_id' => 'accesspoint',
        'bill_id' => 'bill',
        'device_group_id' => 'device_group',
        'location_id' => 'location',
    ];

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
     * @param  array<string, mixed>  $element
     * @return array<int, string>
     */
    public static function extractIdFieldsForFault(array $element): array
    {
        return array_filter(array_keys($element), fn ($key) =>
            // Exclude location_id as it is not relevant for the comparison
            ($key === 'id' || strpos((string) $key, '_id')) !== false && $key !== 'location_id');
    }

    /**
     * @param  array<string, mixed>  $element
     * @param  array<int, string>  $idFields
     */
    public static function generateComparisonKeyForFault(array $element, array $idFields): string
    {
        $keyParts = [];
        foreach ($idFields as $field) {
            $keyParts[] = $element[$field] ?? '';
        }

        return implode('|', $keyParts);
    }

    /**
     * Stable entity identity for alert rule rows (alert_problems.entity_key / morph resolution).
     * Ordered: first matching profile wins. Add entries here instead of one-off row checks.
     *
     * @return list<array{
     *     key_prefix: string,
     *     trigger: string,
     *     columns: list<string>,
     *     model?: class-string<Model>|null,
     *     alias?: string|null,
     *     id_column?: string|null,
     *     lookup_columns?: list<string>|null,
     *     skip_if_model_matches?: class-string<Model>|null,
     *     entity_only?: bool,
     * }>
     */
    private static function entityIdentityRegistry(): array
    {
        return [
            [
                'key_prefix' => 'bgppeer',
                'trigger' => 'bgpPeerIdentifier',
                'columns' => ['device_id', 'context_name', 'bgpPeerIdentifier'],
                'model' => BgpPeer::class,
                'alias' => 'bgppeer',
                'id_column' => 'bgpPeer_id',
                'lookup_columns' => ['device_id', 'context_name', 'bgpPeerIdentifier'],
            ],
            [
                'key_prefix' => 'sensor',
                'trigger' => 'sensor_index',
                'columns' => ['device_id', 'poller_type', 'sensor_class', 'sensor_type', 'sensor_index'],
                'model' => Sensor::class,
                'alias' => 'sensor',
                'id_column' => 'sensor_id',
                'lookup_columns' => ['poller_type', 'sensor_class', 'sensor_type', 'sensor_index'],
                'skip_if_model_matches' => WirelessSensor::class,
            ],
            [
                'key_prefix' => 'wireless_sensor',
                'trigger' => 'sensor_index',
                'columns' => ['device_id', 'poller_type', 'sensor_class', 'sensor_type', 'sensor_index'],
                'model' => WirelessSensor::class,
                'alias' => null,
                'id_column' => 'sensor_id',
                'lookup_columns' => ['poller_type', 'sensor_class', 'sensor_type', 'sensor_index'],
                'entity_only' => true,
            ],
            [
                'key_prefix' => 'mempool',
                'trigger' => 'mempool_index',
                'columns' => ['device_id', 'mempool_type', 'mempool_class', 'mempool_index'],
                'model' => Mempool::class,
                'alias' => 'mempool',
                'id_column' => 'mempool_id',
                'lookup_columns' => ['mempool_type', 'mempool_class', 'mempool_index'],
            ],
            [
                'key_prefix' => 'processor',
                'trigger' => 'processor_index',
                'columns' => ['device_id', 'processor_type', 'processor_index'],
                'model' => Processor::class,
                'alias' => 'processor',
                'id_column' => 'processor_id',
                'lookup_columns' => ['processor_type', 'processor_index'],
            ],
        ];
    }

    /**
     * Stable reconciliation key for a rule result row (stored as alert_problems.entity_key).
     *
     * @param  array<string, mixed>  $row
     */
    public static function faultKeyForRow(array $row): string
    {
        $profile = self::matchIdentityProfile($row, forEntityKey: true);
        if ($profile !== null) {
            return self::faultKeyFromProfile($row, $profile);
        }

        return self::generateComparisonKeyForFault($row, self::extractIdFieldsForFault($row));
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{0: ?string, 1: ?int}
     */
    public static function entityForFault(array $row): array
    {
        $profile = self::matchIdentityProfile($row, forEntityKey: false);
        if ($profile !== null) {
            return self::entityFromProfile($row, $profile);
        }

        foreach (self::ENTITY_COLUMN_MAP as $column => $alias) {
            if (! empty($row[$column])) {
                return [$alias, (int) $row[$column]];
            }
        }

        foreach ($row as $key => $value) {
            if ($key === 'device_id' || $key === 'location_id' || empty($value)) {
                continue;
            }
            if ($key === 'id' || str_ends_with((string) $key, '_id')) {
                $alias = $key === 'id' ? null : substr((string) $key, 0, -3);

                return [$alias, (int) $value];
            }
        }

        return [null, null];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    private static function matchIdentityProfile(array $row, bool $forEntityKey): ?array
    {
        foreach (self::entityIdentityRegistry() as $profile) {
            if (($profile['entity_only'] ?? false) && $forEntityKey) {
                continue;
            }

            if (! self::identityTriggerPresent($row, $profile['trigger'])) {
                continue;
            }

            if (! $forEntityKey) {
                $skipModel = $profile['skip_if_model_matches'] ?? null;
                if ($skipModel !== null && self::lookupEntityPrimaryKey($skipModel, $row, self::lookupColumnsForProfile($profile)) !== null) {
                    continue;
                }
            }

            return $profile;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $profile
     */
    private static function identityTriggerPresent(array $row, string $trigger): bool
    {
        if (! array_key_exists($trigger, $row)) {
            return false;
        }

        $value = $row[$trigger];

        return $value !== null && $value !== '';
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $profile
     */
    private static function faultKeyFromProfile(array $row, array $profile): string
    {
        $parts = [(string) $profile['key_prefix']];
        foreach ($profile['columns'] as $column) {
            $parts[] = (string) ($row[$column] ?? '');
        }

        return implode('|', $parts);
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $profile
     * @return array{0: ?string, 1: ?int}
     */
    private static function entityFromProfile(array $row, array $profile): array
    {
        $alias = $profile['alias'] ?? null;
        $modelClass = $profile['model'] ?? null;
        if ($modelClass !== null) {
            $entityId = self::lookupEntityPrimaryKey($modelClass, $row, self::lookupColumnsForProfile($profile));
            if ($entityId !== null && $alias !== null) {
                return [$alias, $entityId];
            }
        }

        $idColumn = $profile['id_column'] ?? null;
        if ($alias !== null && $idColumn !== null && ! empty($row[$idColumn])) {
            return [$alias, (int) $row[$idColumn]];
        }

        return [null, null];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return list<string>
     */
    private static function lookupColumnsForProfile(array $profile): array
    {
        return $profile['lookup_columns'] ?? $profile['columns'];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $row
     * @param  list<string>  $identityColumns
     */
    private static function lookupEntityPrimaryKey(string $modelClass, array $row, array $identityColumns): ?int
    {
        $deviceId = (int) ($row['device_id'] ?? 0);
        if ($deviceId <= 0) {
            return null;
        }

        try {
            $query = $modelClass::query()->where('device_id', $deviceId);

            foreach ($identityColumns as $field) {
                if ($field === 'device_id') {
                    continue;
                }
                if (! array_key_exists($field, $row)) {
                    continue;
                }
                $value = $row[$field];
                if ($field === 'context_name' && ($value === null || $value === '')) {
                    $query->where(function ($q): void {
                        $q->where('context_name', '')->orWhereNull('context_name');
                    });
                    continue;
                }
                if ($value !== null && $value !== '') {
                    $query->where($field, $value);
                }
            }

            $id = $query->value((new $modelClass)->getKeyName());

            return $id !== null ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
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
     * @return array<int, int> segment ids due to fire this cycle
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
     * @return array{0: array<int, int>, 1: array<string, array{fires:int, last:int}>} [due segment ids, updated state]
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
        $segmentIds = array_values(array_unique(array_map(intval(...), $segmentIds)));
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
            if ($email) {
                $contacts[$email] = '';
            }
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
