<?php

namespace App\Http\Controllers\Table;

use App\Facades\DeviceCache;
use App\Facades\PortCache;
use App\Models\AlertLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

class AlertLogController extends TableController
{
    protected $default_sort = ['time_logged' => 'asc'];

    protected function rules()
    {
        return [
            'severity' => 'array|nullable',
            'severity.*' => 'integer',
        ];
    }

    protected function sortFields($request)
    {
        return [
            'time_logged',
            'status' => 'state',
            'alert_rule' => 'name',
            'severity',
            'hostname',
        ];
    }

    protected function searchFields(Request $request)
    {
        return [
            'device' => ['hostname', 'sysname'],
            'rule' => ['name'],
//            'time_logged', // how would this be useful? removed
        ];
    }

    protected function filterFields(Request $request)
    {
        return [
            'device_id',
            'severity' => function (Builder $q, ?array $severity) {
                if ($severity) {
                    $q->whereHas('rule', fn ($q) => $q->whereIn('severity', array_map(intval(...), $severity)));
                }
            },
            'state',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function baseQuery(Request $request)
    {
        $query = AlertLog::query()
            ->select('alert_log.*')
            ->with(['device', 'rule'])
            ->hasAccess($request->user());

        $sort = $request->get('sort');
        if (isset($sort['severity']) || isset($sort['alert_rule'])) {
            $query->leftJoin('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id');
        }
        if (isset($sort['hostname'])) {
            $query->leftJoin('devices', 'alert_log.device_id', '=', 'devices.device_id');
        }

        return $query;
    }

    /**
     * @param AlertLog $model
     * @return array
     */
    public function formatItem($model): array
    {
        $fault_detail = $this->getFaultDetail($model);
        $status = Html::severityToLabel($model->state->asSeverity(), title: $model->state->name, class: 'alert-status');

        return [
            'id' => $model->id,
            'time_logged' => $model->time_logged,
            'details' => '<a class="fa fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $model->id . '" data-parent="#alerts"></a>',
            'verbose_details' => "<button type='button' class='btn btn-alert-details verbose-alert-details' style='display:none' aria-label='Details' id='alert-details' data-alert_log_id='$model->id'><i class='fa-solid fa-circle-info'></i></button>",
            'hostname' => '<div class="incident">' . Url::modernDeviceLink($model->device) . '<div id="incident' . $model->id . '" class="collapse">' . $fault_detail . '</div></div>',
            'alert_rule' => $model->rule?->name,
            'status' => $status,
            'severity' => $model->rule?->severity,
        ];
    }

    protected function getExportHeaders(): array
    {
        return [
            'id',
            'state',
            'time_logged',
            'device_id',
            'device',
            'rule_id',
            'rule_name',
            'rule_severity',
            'details',
        ];
    }

    protected function formatExportRow($item): array
    {
        /** @var \Carbon\Carbon $time_logged */
        $time_logged = $item->time_logged;
        return [
            $item->id,
            strtolower($item->state->name),
            $time_logged->toIso8601ZuluString(),
            $item->device_id,
            $item->device?->displayName(),
            $item->rule_id,
            $item->rule?->name,
            $item->rule?->severity,
            json_encode($item->details),
        ];
    }

    private function getFaultDetail(AlertLog $model): string
    {
        $details = $model->details;

        $max_row_length = 0;
        $all_fault_detail = '';

        // Check if we have a diff (alert status changed, worse and better)
        if (isset($details['diff'])) {
            // Add a "title" for the modifications
            $all_fault_detail .= '<b>Modifications:</b><br>';

            // Check if we have added
            if (isset($details['diff']['added'])) {
                foreach (array_values($details['diff']['added'] ?? []) as $oa => $tmp_alerts_added) {
                    $fault_detail = $this->formatDetails($oa, $tmp_alerts_added, 'Added');
                    $max_row_length = strlen(strip_tags((string) $fault_detail)) > $max_row_length ? strlen(strip_tags((string) $fault_detail)) : $max_row_length;
                    $all_fault_detail .= $fault_detail;
                }//end foreach
            }

            // Check if we have resolved
            if (isset($details['diff']['resolved'])) {
                foreach (array_values($details['diff']['resolved'] ?? []) as $or => $tmp_alerts_resolved) {
                    $fault_detail = $this->formatDetails($or, $tmp_alerts_resolved, 'Resolved');
                    $max_row_length = strlen(strip_tags((string) $fault_detail)) > $max_row_length ? strlen(strip_tags((string) $fault_detail)) : $max_row_length;
                    $all_fault_detail .= $fault_detail;
                }//end foreach
            }

            // Add a "title" for the complete list
            $all_fault_detail .= '<br><b>All current items:</b><br>';
        }

        foreach ($details['rule'] ?? [] as $o => $tmp_alerts_rule) {
            $fault_detail = $this->formatDetails($o, $tmp_alerts_rule);
            $max_row_length = strlen(strip_tags((string) $fault_detail)) > $max_row_length ? strlen(strip_tags((string) $fault_detail)) : $max_row_length;
            $all_fault_detail .= $fault_detail;
        }//end foreach

        return $all_fault_detail;
    }

    function formatDetails($alert_idx, $tmp_alerts, $type_info = null)
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
            if (! empty($tmp_alerts['isisISAdjState'])) {
                $fault_detail .= 'Adjacent ' . $tmp_alerts['isisISAdjIPAddrAddress'];
                $port = PortCache::get($tmp_alerts['port_id']);
                $fault_detail .= ', Interface ' . Url::portLink($port);
            } else {
                $tmp_alerts = cleanPort($tmp_alerts);
                $fault_detail .= generate_port_link($tmp_alerts) . ';&nbsp;';
            }
            if ((isset($tmp_alerts['ifDescr'])) && (isset($tmp_alerts['ifAlias'])) && ($tmp_alerts['ifDescr'] != $tmp_alerts['ifAlias'])) {
                // IfAlias has been set, so display it on alarms
                $fault_detail .= $tmp_alerts['ifAlias'] . '; ';
                unset($tmp_alerts['label']);
            }
            $fallback = false;
        }

        if (isset($tmp_alerts['accesspoint_id'])) {
            $fault_detail .= generate_ap_link($tmp_alerts, $tmp_alerts['name']) . ';&nbsp;';
            $fallback = false;
        }

        if (isset($tmp_alerts['sensor_id'])) {
            if ($tmp_alerts['sensor_class'] == 'state') {
                // Give more details for a state (textual form)
                $details = 'State: ' . ($tmp_alerts['state_descr'] ?? '') . ' (numerical ' . $tmp_alerts['sensor_current'] . ')<br>  ';
            } else {
                // Other sensors
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

            $fault_detail .= generate_sensor_link($tmp_alerts, $tmp_alerts['name'] ?? '') . ';&nbsp; <br>' . $details;
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
            $fault_detail .= 'Service Host: ' . ($tmp_alerts['service_ip'] != '' ? $tmp_alerts['service_ip'] : DeviceCache::get($tmp_alerts['device_id'])->displayName()) . ',<br>';
            $fault_detail .= ($tmp_alerts['service_desc'] != '') ? ('Description: ' . $tmp_alerts['service_desc'] . ',<br>') : '';
            $fault_detail .= ($tmp_alerts['service_param'] != '') ? ('Param: ' . $tmp_alerts['service_param'] . ',<br>') : '';
            $fault_detail .= 'Msg: ' . $tmp_alerts['service_message'];
            $fallback = false;
        }

        if (isset($tmp_alerts['bgpPeer_id'])) {
            // If we have a bgpPeer_id, we format the data accordingly
            $fault_detail .= "BGP peer <a href='" .
                Url::generate([
                    'page' => 'device',
                    'device' => $tmp_alerts['device_id'],
                    'tab' => 'routing',
                    'proto' => 'bgp',
                ]) .
                "'>" . $tmp_alerts['bgpPeerIdentifier'] . '</a>';
            $fault_detail .= ', Desc ' . $tmp_alerts['bgpPeerDescr'] ?? '';
            $fault_detail .= ', AS' . $tmp_alerts['bgpPeerRemoteAs'];
            $fault_detail .= ', State ' . $tmp_alerts['bgpPeerState'];
            $fallback = false;
        }

        if (isset($tmp_alerts['mempool_id'])) {
            // If we have a mempool_id, we format the data accordingly
            $fault_detail .= "MemoryPool <a href='" .
                Url::generate([
                    'page' => 'graphs',
                    'id' => $tmp_alerts['mempool_id'],
                    'type' => 'mempool_usage',
                ]) .
                "'>" . ($tmp_alerts['mempool_descr'] ?? 'link') . '</a>';
            $fault_detail .= '<br> &nbsp; &nbsp; &nbsp; Usage ' . $tmp_alerts['mempool_perc'] . '%, &nbsp; Free ' . Number::formatSi($tmp_alerts['mempool_free']) . ',&nbsp; Size ' . Number::formatSi($tmp_alerts['mempool_total']);
            $fallback = false;
        }

        if ($tmp_alerts['type'] && isset($tmp_alerts['label'])) {
            $fault_detail .= ' ' . $tmp_alerts['type'] . ' - ' . $tmp_alerts['label'];
            if (! empty($tmp_alerts['error'])) {
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
                if (! empty($v) && Str::contains($k, ['id', 'desc', 'msg', 'last'], ignoreCase: true)) {
                    $fault_detail_data[] = "$k => '$v'";
                }
            }
            $fault_detail .= count($fault_detail_data) ? implode('<br>&nbsp;&nbsp;&nbsp', $fault_detail_data) : '';

            $fault_detail = rtrim($fault_detail, ', ');
        }

        $fault_detail .= '<br>';

        return $fault_detail;
    }

}
