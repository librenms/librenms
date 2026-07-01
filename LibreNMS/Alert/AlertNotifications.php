<?php

/**
 * AlertNotifications.php
 *
 * -Description-
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Alert;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\AlertTransport;
use App\Models\Eventlog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Enum\AlertRuleOperationPhase;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\MaintenanceStatus;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Util\Number;
use LibreNMS\Util\Time;

class AlertNotifications
{
    /**
     * Cache of valid alert rule ids by device.
     *
     * @var array<int, array<int, true>>
     */
    private array $rulesCache = [];

    /**
     * Cache of AlertTransport models.
     *
     * @var Collection<int, AlertTransport>|null
     */
    private ?Collection $transports = null;

    /**
     * Get Notification Data
     *
     * @param  Alert  $alert
     * @return array|bool
     */
    public function getNotificationData(Alert $alert): array|bool
    {
        $device = $alert->device;
        if (! $device) {
            return false;
        }

        $rule = $alert->rule;
        $log = $alert->latestLog;
        if (! $log) {
            return false;
        }

        $obj = [];
        $obj['hostname'] = $device->hostname;
        $obj['sysName'] = $device->sysName;
        $obj['display'] = $device->displayName();
        $obj['sysDescr'] = $device->sysDescr;
        $obj['sysContact'] = $device->sysContact;
        $obj['os'] = $device->os;
        $obj['type'] = $device->type;
        $obj['ip'] = $device->ip;
        $obj['device_groups'] = $device->groups->pluck('name', 'id')->all();
        $obj['hardware'] = $device->hardware;
        $obj['version'] = $device->version;
        $obj['serial'] = $device->serial;
        $obj['features'] = $device->features;
        $obj['location'] = (string) $device->location;
        $obj['uptime'] = $device->uptime;
        $obj['uptime_short'] = Time::formatInterval($device->uptime, true);
        $obj['uptime_long'] = Time::formatInterval($device->uptime);
        $obj['description'] = $device->purpose;
        $obj['notes'] = $device->notes;
        $obj['alert_notes'] = $alert->note;
        $obj['device_id'] = $device->device_id;
        $obj['rule_id'] = $alert->rule_id;
        $obj['id'] = $log->id;
        $obj['proc'] = $rule->proc;
        $obj['status'] = $device->status;
        $obj['status_reason'] = $device->status_reason;

        if ((new ConnectivityHelper($device))->icmpIsEnabled()) {
            try {
                $last_ping = Rrd::lastUpdate(Rrd::name($device->hostname, 'icmp-perf'));
                if ($last_ping) {
                    $obj['ping_timestamp'] = $last_ping->timestamp;
                    $obj['ping_loss'] = Number::calculatePercent($last_ping->get('xmt') - $last_ping->get('rcv'), $last_ping->get('xmt'));
                    $obj['ping_min'] = $last_ping->get('min');
                    $obj['ping_max'] = $last_ping->get('max');
                    $obj['ping_avg'] = $last_ping->get('avg');
                    $obj['debug'] = 'unsupported';
                }
            } catch (RrdException $e) {
                Log::error("Error getting last ping for device {$device->hostname}: {$e->getMessage()}");
            }
        }

        $extra = $log->details;
        $obj['applications'] = $device->applications->groupBy('app_type');
        $obj['applications_metrics'] = [];
        foreach ($obj['applications'] as $app_name => $app_instances) {
            $obj['applications_metrics'][$app_name] = [];
            foreach ($app_instances as $app) {
                $rendered_app_metrics = [];
                foreach ($app->metrics as $metric) {
                    $rendered_app_metrics[$metric->metric] = [
                        'value' => $metric->value,
                        'value_prev' => $metric->value_prev,
                    ];
                }
                $obj['applications_metrics'][$app_name][] = $rendered_app_metrics;
            }
        }

        $template_manager = new Template();
        $template = $template_manager->getTemplate($obj);

        if ($alert->state >= AlertState::ACTIVE) {
            $obj['title'] = $template->title ?: 'Alert for device ' . $obj['display'] . ' - ' . $rule->name;
            if ($alert->state == AlertState::ACKNOWLEDGED) {
                $obj['title'] .= ' Has been acknowledged';
            } elseif ($alert->state == AlertState::WORSE) {
                $obj['title'] .= ' Has worsened';
            } elseif ($alert->state == AlertState::BETTER) {
                $obj['title'] .= ' Has improved';
            } elseif ($alert->state == AlertState::CHANGED) {
                $obj['title'] .= ' changed';
            }

            $i = 0;
            foreach (($extra['rule'] ?? []) as $incident) {
                $i++;
                $obj['faults'][$i] = $incident;
                $obj['faults'][$i]['string'] = null;
                foreach ($incident as $k => $v) {
                    if (! empty($v) && $k != 'device_id' && (stristr((string) $k, 'id') || stristr((string) $k, 'desc') || stristr((string) $k, 'msg')) && substr_count((string) $k, '_') <= 1) {
                        $obj['faults'][$i]['string'] .= $k . ' = ' . $v . '; ';
                    }
                }
            }
            $obj['elapsed'] = Time::formatInterval(time() - $log->time_logged->getTimestamp(), true) ?: 'none';
            if (! empty($extra['diff'])) {
                $obj['diff'] = $extra['diff'];
            }
        } elseif ($alert->state == AlertState::RECOVERED) {
            $previousLog = AlertLog::whereNotIn('state', [AlertState::ACKNOWLEDGED, AlertState::RECOVERED])
                ->where('rule_id', $alert->rule_id)
                ->where('device_id', $alert->device_id)
                ->where('id', '<', $log->id)
                ->latest('id')
                ->first();

            if (! $previousLog) {
                return false;
            }

            $extra = $previousLog->details;
            // Reset count to 0 so alerts will continue
            $extra['count'] = 0;
            $log->update(['details' => $extra]);

            $obj['title'] = $template->title_rec ?: 'Device ' . $obj['display'] . ' recovered from ' . ($rule->name ?: $rule->builder);
            $obj['elapsed'] = Time::formatInterval($log->time_logged->getTimestamp() - $previousLog->time_logged->getTimestamp(), true) ?: 'none';
            $obj['id'] = $previousLog->id;

            $i = 0;
            foreach (($extra['rule'] ?? []) as $incident) {
                $i++;
                $obj['faults'][$i] = $incident;
                $obj['faults'][$i]['string'] = '';
                foreach ($incident as $k => $v) {
                    if (! empty($v) && $k != 'device_id' && (stristr((string) $k, 'id') || stristr((string) $k, 'desc') || stristr((string) $k, 'msg')) && substr_count((string) $k, '_') <= 1) {
                        $obj['faults'][$i]['string'] .= $k . ' => ' . $v . '; ';
                    }
                }
            }
        } else {
            return false;
        }

        $obj['builder'] = $rule->builder;
        $obj['uid'] = $log->id;
        $obj['alert_id'] = $alert->id;
        $obj['severity'] = $rule->severity;
        $obj['rule'] = $rule->builder;
        $obj['name'] = $rule->name;
        $obj['timestamp'] = $log->time_logged->toDateTimeString();
        $obj['contacts'] = $extra['contacts'] ?? [];
        $obj['state'] = $alert->state;
        $obj['alerted'] = $alert->alerted;
        $obj['template'] = $template;

        $obj['operation_phase'] = AlertUtil::mapAlertStateToOperationPhase((int) $alert->state);
        $detailCount = (int) ($extra['count'] ?? 0);
        $obj['escalation_step'] = max(1, $detailCount);
        if ($obj['operation_phase'] !== AlertRuleOperationPhase::PROBLEM) {
            $obj['escalation_step'] = 1;
        }

        return $obj;
    }

    public function clearStaleAlerts(): void
    {
        Alert::where('state', '!=', AlertState::CLEAR)
            ->where(function ($query): void {
                $query->whereDoesntHave('device')
                    ->orWhereDoesntHave('rule');
            })
            ->get()
            ->each(function (Alert $alert): void {
                echo "Stale-alert: #{$alert->id}" . PHP_EOL;
                $alert->delete();
            });
    }

    /**
     * Re-Validate Rule-Mappings
     *
     * @param  int  $device_id  Device-ID
     * @param  int  $rule_id  Rule-ID
     * @return bool
     */
    public function isRuleValid(int $device_id, int $rule_id): bool
    {
        if (! isset($this->rulesCache[$device_id])) {
            $device = DeviceCache::get($device_id);
            if (! $device->exists) {
                return false;
            }

            $this->rulesCache[$device_id] = [];

            foreach (AlertRule::enabled()->forDevice($device)->pluck('id') as $id) {
                $this->rulesCache[$device_id][$id] = true;
            }
        }

        return isset($this->rulesCache[$device_id][$rule_id]);
    }

    /**
     * Issue Alert-Object
     *
     * @param  Alert  $alert
     * @return bool
     */
    public function issueAlert(Alert $alert): bool
    {
        $rule = $alert->rule;
        $log = $alert->latestLog;

        if (LibrenmsConfig::get('alert.fixed-contacts') == false) {
            $sql = $rule->query ?: QueryBuilderParser::fromJson($rule->builder)->toSql();
            $rows = DB::select($sql, [$alert->device_id]);
            $rows = array_map(fn ($row) => (array) $row, $rows);

            $details = $log->details;
            $details['contacts'] = AlertUtil::getContacts($rows);
            $log->update(['details' => $details]);
        }

        $obj = $this->getNotificationData($alert);
        if (is_array($obj)) {
            echo "Issuing Alert-UID #{$log->id}/{$alert->state}:" . PHP_EOL;
            if ($alert->state != AlertState::ACKNOWLEDGED || LibrenmsConfig::get('alert.acknowledged') === true) {
                $this->extTransports($obj);
            }
            echo "\r\n";
        }

        return true;
    }

    /**
     * Issue ACK notification
     *
     * @return void
     */
    public function runAcks(): void
    {
        $this->loadAlerts('alerts.state = ' . AlertState::ACKNOWLEDGED . ' && alerts.open = ' . AlertState::ACTIVE)
            ->each(function (Alert $alert): void {
                $rextra = $alert->rule->extra;
                if ($rextra['acknowledgement'] ?? true) {
                    $this->issueAlert($alert);
                }
                $alert->update(['open' => 0]);
            });
    }

    /**
     * Run Follow-Up alerts
     *
     * @return void
     */
    public function runFollowUp(): void
    {
        $this->loadAlerts('alerts.state > ' . AlertState::CLEAR . ' && alerts.open = 0')
            ->each(function (Alert $alert): void {
                $this->processFollowUp($alert);
            });
    }

    /**
     * Process a follow-up alert.
     *
     * @param  Alert  $alert
     * @return void
     */
    private function processFollowUp(Alert $alert): void
    {
        $rule = $alert->rule;
        $rextra = $rule->extra;

        if ($alert->state == AlertState::ACKNOWLEDGED && ($alert->info['until_clear'] ?? true)) {
            return;
        }

        if ($rextra['invert'] ?? false) {
            return;
        }

        $sql = $rule->query ?: QueryBuilderParser::fromJson($rule->builder)->toSql();
        $chk = DB::select($sql, [$alert->device_id]);

        $chk = array_map(function ($row) {
            $row = (array) $row;
            if (isset($row['ip'])) {
                $row['ip'] = inet6_ntop($row['ip']);
            }

            return $row;
        }, $chk);

        $current_alert_count = count($chk);
        $details = $alert->latestLog->details;
        $previous_faults = $details['rule'] ?? [];
        $previous_alert_count = count($previous_faults);

        [$added_diff, $resolved_diff] = $this->diffBetweenFaults($previous_faults, $chk);

        $ret = "Alert #{$alert->latestLog->id}";
        $state = AlertState::CLEAR;

        if (! empty($added_diff) && ! empty($resolved_diff)) {
            $ret .= ' Changed';
            $state = AlertState::CHANGED;
            $details['diff'] = ['added' => $added_diff, 'resolved' => $resolved_diff];
        } elseif (! empty($added_diff)) {
            $ret .= ' Worse';
            $state = AlertState::WORSE;
            $details['diff'] = ['added' => $added_diff];
        } elseif (! empty($resolved_diff)) {
            $ret .= ' Better';
            $state = AlertState::BETTER;
            $details['diff'] = ['resolved' => $resolved_diff];
        } elseif ($current_alert_count > $previous_alert_count) {
            $ret .= ' Worse';
            $state = AlertState::WORSE;
            Eventlog::log('Alert got worse but the diff was not, ensure that a "id" or "_id" field is available for rule ' . $rule->name, $alert->device_id, 'alert', Severity::Warning);
        } elseif ($current_alert_count < $previous_alert_count) {
            $ret .= ' Better';
            $state = AlertState::BETTER;
            Eventlog::log('Alert got better but the diff was not, ensure that a "id" or "_id" field is available for rule ' . $rule->name, $alert->device_id, 'alert', Severity::Warning);
        }

        if ($state > AlertState::CLEAR && $current_alert_count > 0) {
            $details['rule'] = $chk;
            if ($alert->device->alertLogs()->create([
                'state' => $state,
                'rule_id' => $alert->rule_id,
                'details' => $details,
            ])) {
                $alert->update(['state' => $state, 'open' => 1, 'alerted' => 1]);
            }

            echo $ret . ' (' . $previous_alert_count . '/' . $current_alert_count . ")\r\n";
        }
    }

    /**
     * Extract the fields that are used to identify the elements in the array of a "fault"
     *
     * @param  array  $element
     * @return array
     */
    private function extractIdFieldsForFault($element)
    {
        return array_filter(array_keys($element), fn ($key) =>
            // Exclude location_id as it is not relevant for the comparison
            ($key === 'id' || str_ends_with((string) $key, '_id')) && $key !== 'location_id');
    }

    /**
     * Generate a comparison key for an element based on the fields that identify it for a "fault"
     *
     * @param  array  $element
     * @param  array  $idFields
     * @return string
     */
    private function generateComparisonKeyForFault($element, $idFields)
    {
        $keyParts = [];
        foreach ($idFields as $field) {
            $keyParts[] = $element[$field] ?? '';
        }

        return implode('|', $keyParts);
    }

    /**
     * Find new elements in the array for faults
     * PHP array_diff is not working well for it
     *
     * @param  array  $array1
     * @param  array  $array2
     * @return array [$added, $removed]
     */
    private function diffBetweenFaults($array1, $array2)
    {
        $array1_keys = [];
        $added_elements = [];
        $removed_elements = [];

        // Create associative array for quick lookup of $array1 elements
        foreach ($array1 as $element1) {
            $element1_ids = $this->extractIdFieldsForFault($element1);
            $element1_key = $this->generateComparisonKeyForFault($element1, $element1_ids);
            $array1_keys[$element1_key] = $element1;
        }

        // Iterate through $array2 and determine added elements
        foreach ($array2 as $element2) {
            $element2_ids = $this->extractIdFieldsForFault($element2);
            $element2_key = $this->generateComparisonKeyForFault($element2, $element2_ids);

            if (! isset($array1_keys[$element2_key])) {
                $added_elements[] = $element2;
            } else {
                // Remove matched elements
                unset($array1_keys[$element2_key]);
            }
        }

        // Remaining elements in $array1_keys are the removed elements
        $removed_elements = array_values($array1_keys);

        return [$added_elements, $removed_elements];
    }

    /**
     * Load alerts based on the given where clause.
     *
     * @param  string  $where
     * @return Collection<int, Alert>
     */
    public function loadAlerts(string $where): Collection
    {
        return Alert::whereRaw($where)
            ->with(['rule', 'latestLog', 'device'])
            ->get()
            ->filter(function (Alert $alert) {
                if (! $alert->rule || $alert->rule->disabled || ! $this->isRuleValid((int) $alert->device_id, (int) $alert->rule_id)) {
                    echo 'Stale-Rule: #' . $alert->rule_id . '/' . $alert->device_id . "\r\n";
                    $alert->delete();

                    return false;
                }

                if (! $alert->latestLog) {
                    Log::warning("Alert #{$alert->id} has no log entries, cleaning up");

                    return false;
                }

                return true;
            });
    }

    /**
     * Run all alerts
     *
     * @return void
     */
    public function runAlerts(): void
    {
        $this->loadAlerts('alerts.state != ' . AlertState::ACKNOWLEDGED . ' && alerts.open = 1')
            ->each(function (Alert $alert): void {
                $this->processAlert($alert);
            });
    }

    /**
     * Process a single alert for notification.
     *
     * @param  Alert  $alert
     * @return void
     */
    private function processAlert(Alert $alert): void
    {
        $rule = $alert->rule;
        $log = $alert->latestLog;
        $rextra = $rule->extra;

        if (! isset($rextra['recovery'])) {
            $rextra['recovery'] = true;
        }

        if (in_array($alert->state, [AlertState::ACTIVE, AlertState::WORSE, AlertState::BETTER, AlertState::CHANGED], true)) {
            $details = $log->details;
            AlertUtil::mergeProblemPhaseTimingFromOperations((int) $rule->id, $details, $rextra);
            if ($details !== $log->details) {
                $log->update(['details' => $details]);
            }
        }

        if (! empty($rextra['_stop_notifications'])) {
            echo "Max escalation steps reached for Alert-UID #{$log->id}\r\n";

            return;
        }

        $details = $log->details;
        if (! isset($details['count'])) {
            $details['count'] = 0;
        }

        $device = $alert->device;
        if (! $device) {
            Log::warning("Alert #{$alert->id} references non-existent device {$alert->device_id}, cleaning up");
            $alert->delete();

            return;
        }

        $noiss = ($alert->alerted == $alert->state);
        $noacc = false;
        $updet = false;

        $tolerance_window = (int) LibrenmsConfig::get('alert.tolerance_window');

        if (! empty($rextra['count']) && empty($rextra['interval'])) {
            // This check below is for compat-reasons
            if (! empty($rextra['delay']) && $alert->state != AlertState::RECOVERED) {
                $time_since_log = time() - $log->time_logged->getTimestamp();
                if (($time_since_log + $tolerance_window) < $rextra['delay'] || (! empty($details['delay']) && (time() - $details['delay'] + $tolerance_window) < $rextra['delay'])) {
                    return;
                } else {
                    $details['delay'] = time();
                    $updet = true;
                }
            }

            if ($alert->state == AlertState::ACTIVE && ! empty($rextra['count']) && ($rextra['count'] == -1 || $details['count']++ < $rextra['count'])) {
                if ($details['count'] < $rextra['count'] || $rextra['count'] == -1) {
                    $noacc = true;
                }

                $updet = true;
                $noiss = false;
            }
        } else {
            // This is the new way
            if (! empty($rextra['delay']) && (time() - $log->time_logged->getTimestamp() + $tolerance_window) < $rextra['delay'] && $alert->state != AlertState::RECOVERED) {
                return;
            }

            if (! empty($rextra['interval'])) {
                if (! empty($details['interval']) && (time() - $details['interval'] + $tolerance_window) < $rextra['interval']) {
                    return;
                } else {
                    $details['interval'] = time();
                    $updet = true;
                }
            }

            if (in_array($alert->state, [AlertState::ACTIVE, AlertState::WORSE, AlertState::BETTER, AlertState::CHANGED]) && ! empty($rextra['count']) && ($rextra['count'] == -1 || $details['count']++ < $rextra['count'])) {
                if ($details['count'] < $rextra['count'] || $rextra['count'] == -1) {
                    $noacc = true;
                }

                $updet = true;
                $noiss = false;
            }
        }

        if ($device->ignore || $device->disabled) {
            $noiss = true;
            $updet = false;
            $noacc = false;
        }

        $maintenance_status = $device->getMaintenanceStatus();
        if ($maintenance_status == MaintenanceStatus::MuteAlerts) {
            $noiss = true;
        }

        if ($maintenance_status == MaintenanceStatus::SkipAlerts) {
            $noiss = true;
            $noacc = true;
        }

        if ($updet) {
            $log->update(['details' => $details]);
        }

        if (! empty($rextra['mute'])) {
            echo "Muted Alert-UID #{$log->id}\r\n";
            $noiss = true;
        }

        if ($this->isParentDown((int) $device->device_id)) {
            $noiss = true;
            Eventlog::log('Skipped alerts because all parent devices are down', $device, 'alert', Severity::Ok);
        }

        if ($alert->state == AlertState::RECOVERED && ($rextra['recovery'] ?? true) == false) {
            $noiss = true;
        }

        if (! $noacc && $alert->state == AlertState::CLEAR) {
            $alert->update(['open' => 0]);
        }

        if (! $noiss) {
            $alert->update(['alerted' => $alert->state]);
            $this->issueAlert($alert);
        }
    }

    /**
     * Run external transports
     *
     * @param  array  $obj  Alert-Array
     * @return void
     */
    public function extTransports(array $obj): void
    {
        $this->transports ??= new Collection();
        $template_manager = new Template();

        // If alert transport mapping exists, override the default transports
        $transport_maps = AlertUtil::getAlertTransports(
            $obj['alert_id'],
            $obj['operation_phase'] ?? null,
            (int) ($obj['escalation_step'] ?? 1)
        );

        $ruleId = (int) ($obj['rule_id'] ?? 0);
        if (! $transport_maps || count($transport_maps) === 0) {
            $reason = 'No mapped transport for this operation';
            if ($ruleId > 0 && ! AlertUtil::ruleHasAlertOperations($ruleId)) {
                $reason = 'No operations configured for this rule';
            }

            Eventlog::log($reason . ' (notification skipped)', $obj['device_id'], 'alert', Severity::Notice);
            echo " :: Skipped => $reason";

            return;
        }

        // alerting for default contacts, etc
        if (LibrenmsConfig::get('alert.transports.mail') === true && ! empty($obj['contacts'])) {
            $transport_maps[] = [
                'transport_id' => null,
                'transport_type' => 'mail',
                'transport_name' => 'Default Mail',
            ];
        }

        // Preload missing transports
        $transportIds = collect($transport_maps)->pluck('transport_id')->filter()->unique();
        $missingIds = $transportIds->diff($this->transports->keys());

        if ($missingIds->isNotEmpty()) {
            $this->transports = $this->transports->merge(AlertTransport::whereIn('transport_id', $missingIds)->get()->keyBy('transport_id'));
        }

        foreach ($transport_maps as $item) {
            $class = Transport::getClass($item['transport_type']);
            if (class_exists($class)) {
                $transport_title = "Transport {$item['transport_type']}";
                $obj['transport'] = $item['transport_type'];
                $obj['transport_name'] = $item['transport_name'];
                $obj['alert'] = new AlertData($obj);
                $obj['title'] = $template_manager->getTitle($obj);
                $obj['alert']['title'] = $obj['title'];
                $obj['msg'] = $template_manager->getBody($obj);
                echo " :: $transport_title => ";
                try {
                    $transportModel = $item['transport_id'] ? $this->transports->get($item['transport_id']) : null;
                    $instance = new $class($transportModel);
                    $result = $instance->deliverAlert($obj);
                    $this->alertLog($result, $obj, $obj['transport']);
                } catch (AlertTransportDeliveryException $e) {
                    Eventlog::log($e->getTraceAsString() . PHP_EOL . $e->getMessage(), $obj['device_id'], 'alert', Severity::Error);
                    $this->alertLog($e->getMessage(), $obj, $obj['transport']);
                } catch (\Exception $e) {
                    $this->alertLog($e->getMessage(), $obj, $obj['transport']);
                }
                echo PHP_EOL;
            }
        }
    }

    /**
     * Log alert event
     *
     * @param  mixed  $result
     * @param  array  $obj
     * @param  string  $transport
     * @return void
     */
    public function alertLog($result, array $obj, string $transport): void
    {
        $prefix = [
            AlertState::RECOVERED => 'recovery',
            AlertState::ACKNOWLEDGED => 'acknowledgment',
            AlertState::WORSE => 'worsened',
            AlertState::BETTER => 'improved',
            AlertState::CHANGED => 'changed',
        ];

        if ($obj['state'] == AlertState::ACTIVE) {
            $severity = Severity::tryFrom((int) $obj['severity']) ?? Severity::Unknown;
            $prefix[AlertState::ACTIVE] = $severity->name . ' alert';
        }

        $severity = match ($obj['state']) {
            AlertState::RECOVERED => Severity::Ok,
            AlertState::ACTIVE => Severity::tryFrom((int) $obj['severity']) ?? Severity::Unknown,
            AlertState::ACKNOWLEDGED => Severity::Notice,
            default => Severity::Unknown,
        };

        if ($result === true) {
            echo 'OK';
            Eventlog::log('Issued ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], 'alert', $severity);
        } elseif ($result === false) {
            echo 'ERROR';
            Eventlog::log('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "'", $obj['device_id'], 'alert', Severity::Error);
        } else {
            echo "ERROR: $result\r\n";
            Eventlog::log('Could not issue ' . $prefix[$obj['state']] . " for rule '" . $obj['name'] . "' to transport '" . $transport . "' Error: " . $result, $obj['device_id'], 'alert', Severity::Error);
        }
    }

    /**
     * Check if all parents of a device are down.
     * Returns true if all parents are down
     *
     * @param  int  $device_id  Device-ID
     * @return bool
     */
    public function isParentDown(int $device_id): bool
    {
        $parent_count = DB::table('device_relationships')
            ->where('child_device_id', $device_id)
            ->count();

        if ($parent_count === 0) {
            return false;
        }

        $down_parent_count = DB::table('devices as d')
            ->join('device_relationships as r', 'd.device_id', '=', 'r.parent_device_id')
            ->where('r.child_device_id', $device_id)
            ->where('d.status', 0)
            ->where('d.ignore', 0)
            ->where('d.disabled', 0)
            ->count();

        return $down_parent_count === $parent_count;
    }
}
