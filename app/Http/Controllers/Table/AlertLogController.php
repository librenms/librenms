<?php

namespace App\Http\Controllers\Table;

use App\Http\Parsers\AlertLogDetailParser;
use App\Models\AlertLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

/**
 * @extends TableController<AlertLog>
 */
class AlertLogController extends TableController
{
    protected array $default_sort = ['time_logged' => 'asc'];

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

    protected function sortFields(Request $request): array
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
    protected function baseQuery(Request $request): Builder|\Illuminate\Database\Query\Builder
    {
        $this->authorize('viewAny', AlertLog::class);

        $query = AlertLog::query()
            ->select('alert_log.*')
            ->with(['device', 'rule'])
            ->hasAccess($request->user());

        $query->whereRaw('(
            coalesce((select ar.notify_per_entity from alert_rules ar where ar.id = alert_log.rule_id), 0) = 1
            or alert_log.problem_id is null
            or alert_log.id = (
                select min(al2.id) from alert_log al2
                where al2.device_id = alert_log.device_id
                  and al2.rule_id = alert_log.rule_id
                  and al2.state = alert_log.state
                  and al2.time_logged = alert_log.time_logged
            )
        )');

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
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $details = is_array($model->details) ? $model->details : [];
        $entity_count = 1;
        if ($model->problem_id && $model->rule && ! $model->rule->notify_per_entity) {
            $siblings = AlertLog::query()
                ->where('device_id', $model->device_id)
                ->where('rule_id', $model->rule_id)
                ->where('state', $model->state->value)
                ->where('time_logged', $model->time_logged)
                ->whereNotNull('problem_id')
                ->get(['id', 'details']);
            $entity_count = $siblings->count();
            if ($entity_count > 1) {
                $rows = [];
                foreach ($siblings as $sibling) {
                    foreach ((array) ($sibling->details['rule'] ?? []) as $row) {
                        $rows[] = $row;
                    }
                }
                $details = ['rule' => $rows] + $details;
            }
        }

        $fault_detail = view('alerts.fault-detail', [
            'details' => $this->parser->parse($details),
        ])->render();

        $status = Html::severityToLabel($model->state->asSeverity(), title: $model->state->name, class: 'alert-status');

        $alert_rule = e($model->rule?->name);
        if ($entity_count > 1) {
            $alert_rule .= ' <span class="label label-default">' . $entity_count . '&times;</span>';
        }

        return [
            'id' => $model->id,
            'time_logged' => $model->time_logged,
            'details' => '<a class="fa fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $model->id . '" data-parent="#alerts"></a>',
            'verbose_details' => "<button type='button' class='btn btn-alert-details verbose-alert-details' style='display:none' aria-label='Details' id='alert-details' data-alert_log_id='$model->id'><i class='fa-solid fa-circle-info'></i></button>",
            'hostname' => '<div class="incident">' . Url::modernDeviceLink($model->device) . '<div id="incident' . $model->id . '" class="collapse">' . $fault_detail . '</div></div>',
            'alert_rule' => $alert_rule,
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
