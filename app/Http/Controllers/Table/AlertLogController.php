<?php

namespace App\Http\Controllers\Table;

use App\Models\AlertLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Url;

class AlertLogController extends TableController
{
    protected $default_sort = ['time_logged' => 'desc'];

    public function rules()
    {
        return [
            'device_id' => 'nullable|int',
            'rule_id' => 'nullable|int',
            'state' => 'nullable|int',
            'min_severity' => 'nullable',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'devices.hostname',
            'devices.sysName',
            'alert_rules.name',
        ];
    }

    protected function filterFields($request): array
    {
        return [
            'alert_log.device_id' => 'device_id',
            'alert_log.rule_id' => 'rule_id',
            'state' => function ($query, $state) {
                if ($state !== '-1' && $state !== '' && $state !== null) {
                    return $query->where('alert_log.state', $state);
                }
                return $query;
            },
            'min_severity' => function ($query, $min_severity) {
                // Map the numeric values to the actual enum values based on severity
                if (is_numeric($min_severity) && $min_severity !== '') {
                    $min_severity = (int) $min_severity;
                    $severityEnum = AlertLogSeverity::tryFrom($min_severity);

                    if ($severityEnum === null) {
                        return $query;
                    }

                    $severities = $severityEnum->getSeverities();

                    return match (true) {
                        is_array($severities) => $query->whereIn('alert_rules.severity', $severities),
                        is_string($severities) => $query->where('alert_rules.severity', $severities),
                        default => $query,
                    };
                }
                return $query;
            },
        ];
    }

    protected function sortFields($request): array
    {
        return [
            'time_logged' => 'alert_log.time_logged',
            'hostname' => 'devices.hostname',
            'alert' => 'alert_rules.name',
            'severity' => 'alert_rules.severity',
            'status' => 'alert_log.state',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $query = AlertLog::query()
            ->hasAccess($request->user())
            ->with(['device', 'rule'])
            ->select([
                'alert_log.*',
                'devices.hostname',
                'alert_rules.severity',
                'alert_rules.name as alert_name'
            ])
            ->leftJoin('devices', 'alert_log.device_id', '=', 'devices.device_id')
            ->leftJoin('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id');


        return $query;
    }    /**
     * @param  AlertLog  $alertlog
     */
    public function formatItem($alertlog): array
    {
        static $rulei = 0;
        $rulei++;

        $alert_state = $alertlog->state;
        $fault_detail = '';

        if ($alert_state == '0') {
            // For recovered alerts, get the latest active state details
            $last_active = AlertLog::where('device_id', $alertlog->device_id)
                ->where('id', '<', $alertlog->id)
                ->where('rule_id', $alertlog->rule_id)
                ->where('state', '!=', 0)
                ->orderBy('id', 'desc')
                ->first();

            if ($last_active) {
                $fault_detail = $this->formatAlertDetails($last_active->details);
            } else {
                $fault_detail = 'Rule created, no faults found';
            }
        } else {
            $fault_detail = $this->formatAlertDetails($alertlog->details);
        }

        // Status label styling
        $status_classes = [
            '0' => 'label-success',
            '1' => 'label-danger',
            '2' => 'label-info',
            '3' => 'label-warning',
            '4' => 'label-primary',
            '5' => 'label-warning',
        ];
        $status = $status_classes[$alert_state] ?? 'label-default';

        // Format timestamp with timezone support
        if (session('preferences.timezone')) {
            $formatted_time = Carbon::parse($alertlog->time_logged)
                ->setTimezone(session('preferences.timezone'))
                ->format('Y-m-d H:i:s');
        } else {
            $formatted_time = Carbon::parse($alertlog->time_logged)
                ->format('Y-m-d H:i:s');
        }

        $result = [
            'time_logged' => $formatted_time,
            'details' => '<a class="fa fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $rulei . '" data-parent="#alerts"></a>',
            'hostname' => '<div class="incident">' .
                Url::deviceLink($alertlog->device) .
                '<div id="incident' . $rulei . '" class="collapse">' . $fault_detail . '</div></div>',
            'alert' => htmlspecialchars($alertlog->alert_name ?? $alertlog->rule->name ?? ''),
            'status' => "<i class='alert-status " . $status . "' title='" . ($alert_state ? 'active' : 'recovered') . "'></i>",
            'severity' => $alertlog->severity ?? '',
        ];

        // Verbose button for active alerts
        if (Auth::user()->hasGlobalAdmin() && $alert_state != '0') {
            $result['verbose_details'] = "<button type='button' class='btn btn-alert-details verbose-alert-details' aria-label='Details' id='alert-details' data-alert_log_id='{$alertlog->id}'><i class='fa-solid fa-circle-info'></i></button>";
        }

        return $result;
    }

    private function formatAlertDetails(array $details): string
    {
        $all_fault_detail = '';

        if (isset($details['diff'])) {
            $all_fault_detail .= '<b>Modifications:</b><br>';

            if (isset($details['diff']['added'])) {
                foreach (array_values($details['diff']['added'] ?? []) as $oa => $tmp_alerts_added) {
                    $fault_detail = $this->formatAlertDetailsItem($oa, $tmp_alerts_added, 'Added');
                    $all_fault_detail .= $fault_detail;
                }
            }

            if (isset($details['diff']['resolved'])) {
                foreach (array_values($details['diff']['resolved'] ?? []) as $or => $tmp_alerts_resolved) {
                    $fault_detail = $this->formatAlertDetailsItem($or, $tmp_alerts_resolved, 'Resolved');
                    $all_fault_detail .= $fault_detail;
                }
            }
            $all_fault_detail .= '<br><b>All current items:</b><br>';
        }

        foreach ($details['rule'] ?? [] as $o => $tmp_alerts_rule) {
            $fault_detail = $this->formatAlertDetailsItem($o, $tmp_alerts_rule);
            $all_fault_detail .= $fault_detail;
        }

        return $all_fault_detail;
    }

    private function formatAlertDetailsItem(int $alert_idx, array $tmp_alerts, ?string $type_info = null): string
    {
        $fault_detail = '';
        $fallback = true;
        $fault_detail .= $type_info ? $type_info . '&nbsp;' : '';
        $fault_detail .= '#' . ($alert_idx + 1) . ':&nbsp;';

        if (isset($tmp_alerts['bill_id'])) {
            $fault_detail .= '<a href="' . Url::generate(['page' => 'bill', 'bill_id' => $tmp_alerts['bill_id']], []) . '">' . $tmp_alerts['bill_name'] . '</a>;&nbsp;';
            $fallback = false;
        }

        if (isset($tmp_alerts['port_id'])) {
            if (!empty($tmp_alerts['isisISAdjState'])) {
                $fault_detail .= 'Adjacent ' . $tmp_alerts['isisISAdjIPAddrAddress'];
                $port = \App\Models\Port::find($tmp_alerts['port_id']);
                $fault_detail .= ', Interface ' . Url::portLink($port);
            } else {
                $port = \App\Models\Port::find($tmp_alerts['port_id']);
                if ($port) {
                    $fault_detail .= Url::portLink($port) . ';&nbsp;';
                }
            }
            if ((isset($tmp_alerts['ifDescr'])) && (isset($tmp_alerts['ifAlias'])) && ($tmp_alerts['ifDescr'] != $tmp_alerts['ifAlias'])) {
                $fault_detail .= $tmp_alerts['ifAlias'] . '; ';
                unset($tmp_alerts['label']);
            }
            $fallback = false;
        }

        if (isset($tmp_alerts['accesspoint_id'])) {
            $fault_detail .= 'Access Point: ' . ($tmp_alerts['name'] ?? 'Unknown') . ';&nbsp;';
            $fallback = false;
        }

        if (isset($tmp_alerts['sensor_id'])) {
            if ($tmp_alerts['sensor_class'] == 'state') {
                $details = 'State: ' . ($tmp_alerts['state_descr'] ?? '') . ' (numerical ' . $tmp_alerts['sensor_current'] . ')<br>  ';
            } else {
                $details = 'Value: ' . $tmp_alerts['sensor_current'] . ' (' . $tmp_alerts['sensor_class'] . ')<br>  ';
            }
            $details_a = [];

            if ($tmp_alerts['sensor_limit_low']) {
                $details_a[] = 'low: ' . $tmp_alerts['sensor_limit_low'];
            }
            if ($tmp_alerts['sensor_limit_low_warn']) {
                $details_a[] = 'low_warn: ' . $tmp_alerts['sensor_limit_low_warn'];
            }
            if ($tmp_alerts['sensor_limit_warn']) {
                $details_a[] = 'high_warn: ' . $tmp_alerts['sensor_limit_warn'];
            }
            if ($tmp_alerts['sensor_limit']) {
                $details_a[] = 'high: ' . $tmp_alerts['sensor_limit'];
            }
            $details .= implode(', ', $details_a);

            $sensor = \App\Models\Sensor::find($tmp_alerts['sensor_id']);
            if ($sensor) {
                $fault_detail .= Url::sensorLink($sensor, $tmp_alerts['name'] ?? '') . ';&nbsp; <br>' . $details;
            } else {
                $fault_detail .= ($tmp_alerts['name'] ?? '') . ';&nbsp; <br>' . $details;
            }
            $fallback = false;
        }

        if (isset($tmp_alerts['service_id'])) {
            $fault_detail .= "Service: <a href='" .
                Url::generate([
                    'page' => 'device',
                    'device' => $tmp_alerts['device_id'],
                    'tab' => 'services',
                    'view' => 'detail',
                ]) .
                "'>" . ($tmp_alerts['service_name'] ?? '') . ' (' . $tmp_alerts['service_type'] . ')' . '</a>';
            $fault_detail .= 'Service Host: ' . ($tmp_alerts['service_ip'] != '' ? $tmp_alerts['service_ip'] : 'N/A') . ',<br>';
            $fault_detail .= ($tmp_alerts['service_desc'] != '') ? ('Description: ' . $tmp_alerts['service_desc'] . ',<br>') : '';
            $fault_detail .= ($tmp_alerts['service_param'] != '') ? ('Param: ' . $tmp_alerts['service_param'] . ',<br>') : '';
            $fault_detail .= 'Msg: ' . $tmp_alerts['service_message'];
            $fallback = false;
        }

        if (isset($tmp_alerts['bgpPeer_id'])) {
            $fault_detail .= "BGP peer <a href='" .
                Url::generate([
                    'page' => 'device',
                    'device' => $tmp_alerts['device_id'],
                    'tab' => 'routing',
                    'proto' => 'bgp',
                ]) .
                "'>" . $tmp_alerts['bgpPeerIdentifier'] . '</a>';
            $fault_detail .= ', Desc ' . ($tmp_alerts['bgpPeerDescr'] ?? '');
            $fault_detail .= ', AS' . $tmp_alerts['bgpPeerRemoteAs'];
            $fault_detail .= ', State ' . $tmp_alerts['bgpPeerState'];
            $fallback = false;
        }

        if (isset($tmp_alerts['mempool_id'])) {
            $fault_detail .= "MemoryPool <a href='" .
                Url::generate([
                    'page' => 'graphs',
                    'id' => $tmp_alerts['mempool_id'],
                    'type' => 'mempool_usage',
                ]) .
                "'>" . ($tmp_alerts['mempool_descr'] ?? 'link') . '</a>';
            $fault_detail .= '<br> &nbsp; &nbsp; &nbsp; Usage ' . $tmp_alerts['mempool_perc'] . '%, &nbsp; Free ' . \LibreNMS\Util\Number::formatSi($tmp_alerts['mempool_free']) . ',&nbsp; Size ' . \LibreNMS\Util\Number::formatSi($tmp_alerts['mempool_total']);
            $fallback = false;
        }

        if ($tmp_alerts['type'] && isset($tmp_alerts['label'])) {
            $fault_detail .= ' ' . $tmp_alerts['type'] . ' - ' . $tmp_alerts['label'];
            if (!empty($tmp_alerts['error'])) {
                $fault_detail .= ' - ' . $tmp_alerts['error'];
            }
            $fault_detail .= ';&nbsp;';
            $fallback = false;
        }

        if (in_array('app_id', array_keys($tmp_alerts))) {
            $fault_detail .= "<a href='" .
                Url::generate([
                    'page' => 'device',
                    'device' => $tmp_alerts['device_id'],
                    'tab' => 'apps',
                    'app' => $tmp_alerts['app_type'],
                ]) . "'>";
            $fault_detail .= $tmp_alerts['app_type'];
            $fault_detail .= '</a>';

            if ($tmp_alerts['app_status']) {
                $fault_detail .= ' => ' . $tmp_alerts['app_status'];
            }
            if ($tmp_alerts['metric']) {
                $fault_detail .= ' : ' . $tmp_alerts['metric'] . ' => ' . $tmp_alerts['value'];
            }
            $fallback = false;
        }

        if ($fallback === true) {
            $fault_detail_data = [];
            foreach ($tmp_alerts as $k => $v) {
                if (in_array($k, ['device_id', 'sysObjectID', 'sysDescr', 'location_id'])) {
                    continue;
                }
                if (!empty($v) && \Illuminate\Support\Str::contains($k, ['id', 'desc', 'msg', 'last'], true)) {
                    $fault_detail_data[] = "$k => '$v'";
                }
            }
            $fault_detail .= count($fault_detail_data) ? implode('<br>&nbsp;&nbsp;&nbsp', $fault_detail_data) : '';
            $fault_detail = rtrim($fault_detail, ', ');
        }

        $fault_detail .= '<br>';

        return $fault_detail;
    }

    /**
     * Get headers for CSV export
     *
     * @return array
     */
    protected function getExportHeaders()
    {
        return [
            'Alert ID',
            'State',
            'Timestamp',
            'Device',
            'Alert Rule',
            'Severity',
            'Alert Details',
        ];
    }

    /**
     * Format a row for CSV export
     *
     * @param  AlertLog  $alertlog
     * @return array
     */
    protected function formatExportRow($alertlog)
    {
        // Format timestamp for export
        $date_format = 'Y-m-d H:i:s';
        if (session('preferences.timezone')) {
            $formatted_time = Carbon::parse($alertlog->time_logged)
                ->setTimezone(session('preferences.timezone'))
                ->format($date_format);
        } else {
            $formatted_time = Carbon::parse($alertlog->time_logged)
                ->format($date_format);
        }

        $state_names = [
            '0' => 'Recovered',
            '1' => 'Active',
            '2' => 'Acknowledged',
            '3' => 'Worse',
            '4' => 'Better',
            '5' => 'Warning',
        ];

        // Extract alert details for CSV
        $alert_details = $this->formatAlertDetailsForCsv($alertlog);

        return [
            $alertlog->id,
            $state_names[$alertlog->state] ?? 'Unknown',
            $formatted_time,
            $alertlog->device ? $alertlog->device->displayName() : '',
            $alertlog->alert_name ?? ($alertlog->rule ? $alertlog->rule->name : ''),
            $alertlog->severity ?? '',
            $alert_details,
        ];
    }

    /**
     * Format alert details for CSV export
     *
     * @param  AlertLog  $alertlog
     * @return string
     */
    protected function formatAlertDetailsForCsv($alertlog)
    {
        $details = $alertlog->details;
        $csv_details = [];

        if ($alertlog->state == '0') {
            return 'Alert recovered';
        }

        // Extract rule details
        if (isset($details['rule']) && is_array($details['rule'])) {
            foreach ($details['rule'] as $idx => $rule_item) {
                $item_details = [];

                // For sensor alerts - show current value and thresholds
                if (isset($rule_item['sensor_current'])) {
                    $sensor_class = $rule_item['sensor_class'] ?? 'sensor';
                    $current_value = $rule_item['sensor_current'];
                    $sensor_name = $rule_item['sensor_descr'] ?? ($rule_item['name'] ?? 'Unknown Sensor');

                    if ($sensor_class == 'state') {
                        // For state sensors, try to get the state description
                        $state_descr = $this->getStateDescription($rule_item['sensor_type'] ?? '', $current_value);
                        $item_details[] = "Sensor: {$sensor_name}";
                        $item_details[] = "State: {$state_descr} (value: {$current_value})";
                    } else {
                        $item_details[] = "Sensor: {$sensor_name}";
                        $item_details[] = "Current {$sensor_class}: {$current_value}";
                    }

                    // Add threshold information
                    $thresholds = [];
                    if (isset($rule_item['sensor_limit_low'])) {
                        $thresholds[] = "low: {$rule_item['sensor_limit_low']}";
                    }
                    if (isset($rule_item['sensor_limit_low_warn'])) {
                        $thresholds[] = "low_warn: {$rule_item['sensor_limit_low_warn']}";
                    }
                    if (isset($rule_item['sensor_limit_warn'])) {
                        $thresholds[] = "high_warn: {$rule_item['sensor_limit_warn']}";
                    }
                    if (isset($rule_item['sensor_limit'])) {
                        $thresholds[] = "high: {$rule_item['sensor_limit']}";
                    }

                    if (!empty($thresholds)) {
                        $item_details[] = "Thresholds: " . implode(', ', $thresholds);
                    }
                }

                // For other types of alerts, try to extract meaningful information
                if (empty($item_details)) {
                    foreach ($rule_item as $key => $value) {
                        if (!empty($value) && !in_array($key, ['device_id', 'id']) &&
                            (strpos($key, 'desc') !== false || strpos($key, 'name') !== false ||
                             strpos($key, 'msg') !== false || strpos($key, 'current') !== false)) {
                            $clean_key = str_replace('_', ' ', $key);
                            $item_details[] = ucfirst($clean_key) . ": {$value}";
                        }
                    }
                }

                if (!empty($item_details)) {
                    $csv_details[] = "#" . ($idx + 1) . ": " . implode('; ', $item_details);
                }
            }
        }

        // If no detailed information found, return a default message
        if (empty($csv_details)) {
            return 'No detailed information available';
        }

        return implode(' | ', $csv_details);
    }

    /**
     * Get state description for a given sensor type and value
     *
     * @param string $sensor_type
     * @param int $state_value
     * @return string
     */
    protected function getStateDescription($sensor_type, $state_value)
    {
        if (empty($sensor_type)) {
            return 'Unknown';
        }

        // Query the state translation tables
        $state_translation = DB::table('state_translations')
            ->join('state_indexes', 'state_translations.state_index_id', '=', 'state_indexes.state_index_id')
            ->where('state_indexes.state_name', $sensor_type)
            ->where('state_translations.state_value', $state_value)
            ->select('state_translations.state_descr')
            ->first();

        return $state_translation ? $state_translation->state_descr : 'Unknown';
    }

    /**
     * Get alert details for a specific alert log entry
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function details($id)
    {
        if (!Auth::user()->hasGlobalAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You need to have admin permissions.',
                'details' => 'Wrong permissions'
            ], 403);
        }

        if (!is_numeric($id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid alert id',
                'details' => 'Invalid alert id'
            ], 400);
        }

        $alertlog = AlertLog::where('state', '!=', 2)
            ->where('state', '!=', 0)
            ->where('id', $id)
            ->first();

        if (!$alertlog) {
            return response()->json([
                'status' => 'error',
                'message' => 'No Details found',
                'details' => 'No Details found'
            ], 404);
        }

        try {
            $details = $alertlog->details['rule'] ?? null;

            if (!empty($details)) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Found alert details',
                    'details' => $details
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Details found',
                    'details' => 'No Details found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error parsing alert details',
                'details' => 'Error parsing alert details: ' . $e->getMessage()
            ], 500);
        }
    }
}
