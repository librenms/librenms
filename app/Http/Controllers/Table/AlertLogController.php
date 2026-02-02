<?php

namespace App\Http\Controllers\Table;

use App\Http\Parsers\AlertLogDetailParser;
use App\Models\AlertLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

class AlertLogController extends TableController
{
    protected $default_sort = ['time_logged' => 'asc'];

    public function __construct(
        private readonly AlertLogDetailParser $parser
    ) {
    }

    protected function rules(): array
    {
        return [
            'severity' => 'array|nullable',
            'severity.*' => 'integer',
            'device_id' => 'integer|nullable',
            'device_group' => 'integer|nullable',
            'state' => 'integer|nullable',
        ];
    }

    protected function sortFields($request): array
    {
        return [
            'time_logged',
            'status' => 'state',
            'alert_rule' => 'name',
            'severity',
            'hostname',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'device' => ['hostname', 'sysname'],
            'rule' => ['name'],
            //            'time_logged', // how would this be useful? removed
        ];
    }

    protected function filterFields(Request $request): array
    {
        return [
            'alert_log.device_id' => 'device_id',
            'severity' => function (Builder $q, ?array $severity): void {
                if ($severity) {
                    $q->whereHas('rule', fn ($q) => $q->whereIn('severity', array_map(intval(...), $severity)));
                }
            },
            'device_group' => function ($q, ?int $group_id): void {
                if ($group_id) {
                    $q->inDeviceGroup($group_id);
                }
            },
            'state',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function baseQuery(Request $request): Builder
    {
        $query = AlertLog::query()
            ->select('alert_log.*')
            ->with(['device', 'rule'])
            ->hasAccess($request->user());

        $sort = $request->input('sort');
        if (isset($sort['severity']) || isset($sort['alert_rule'])) {
            $query->leftJoin('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id');
        }
        if (isset($sort['hostname'])) {
            $query->leftJoin('devices', 'alert_log.device_id', '=', 'devices.device_id');
        }

        return $query;
    }

    /**
     * Format alert log item for display
     *
     * @param  AlertLog  $model
     * @return array
     */
    public function formatItem($model): array
    {
        $fault_detail = view('alerts.fault-detail', [
            'details' => $this->parser->parse($model->details),
        ])->render();

        $status = Html::severityToLabel($model->state->asSeverity(), title: $model->state->name, class: 'alert-status');

        return [
            'id' => $model->id,
            'time_logged' => $model->time_logged,
            'details' => '<a class="fa fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $model->id . '" data-parent="#alerts"></a>',
            'verbose_details' => "<button type='button' class='btn btn-alert-details verbose-alert-details' style='display:none' aria-label='Details' id='alert-details' data-alert_log_id='$model->id'><i class='fa-solid fa-circle-info'></i></button>",
            'hostname' => '<div class="incident">' . Url::modernDeviceLink($model->device) . '<div id="incident' . $model->id . '" class="collapse">' . $fault_detail . '</div></div>',
            'alert_rule' => e($model->rule?->name),
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
        return [
            $item->id,
            strtolower((string) $item->state->name),
            $item->time_logged->toIso8601ZuluString(),
            $item->device_id,
            $item->device?->displayName(),
            $item->rule_id,
            $item->rule?->name,
            $item->rule?->severity,
            json_encode($item->details),
        ];
    }
}
