<?php

namespace App\Http\Controllers\Table;

use App\Http\Parsers\AlertLogDetailParser;
use App\Models\AlertLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Alert\AlertUtil;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

/**
 * @extends TableController<AlertLog>
 */
class AlertLogController extends TableController
{
    protected array $default_sort = ['time_logged' => 'asc'];

    /** Max entity rows rendered in the (collapsed) inline detail cell to bound memory. */
    private const INLINE_DETAIL_ROW_LIMIT = 100;

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

        $defaultMax = AlertUtil::defaultMaxEntities();

        // Compute the per-group aggregates (representative row id + fault count) once in a
        // single grouped derived table instead of running correlated subqueries per row.
        $groups = DB::table('alert_log')
            ->selectRaw('device_id, rule_id, state, time_logged, min(id) as min_id, sum(case when fault_id is not null then 1 else 0 end) as fault_count')
            ->groupBy('device_id', 'rule_id', 'state', 'time_logged');

        $query = AlertLog::query()
            ->select('alert_log.*')
            ->with(['device', 'rule'])
            ->hasAccess($request->user())
            ->joinSub($groups, 'alert_log_groups', function ($join): void {
                $join->on('alert_log_groups.device_id', '=', 'alert_log.device_id')
                    ->on('alert_log_groups.rule_id', '=', 'alert_log.rule_id')
                    ->on('alert_log_groups.state', '=', 'alert_log.state')
                    ->on('alert_log_groups.time_logged', '=', 'alert_log.time_logged');
            })
            ->whereRaw('(
                alert_log.fault_id is null
                or alert_log.id = alert_log_groups.min_id
                or (
                    coalesce((select ar.notify_per_entity from alert_rules ar where ar.id = alert_log.rule_id), 0) = 1
                    and alert_log_groups.fault_count <= coalesce((select ar.max_entities from alert_rules ar where ar.id = alert_log.rule_id), ?)
                )
            )', [$defaultMax]);

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

        if ($model->fault_id && $model->rule) {
            // Count siblings without loading/decompressing their details blobs.
            $sibling_query = fn () => AlertLog::query()
                ->where('device_id', $model->device_id)
                ->where('rule_id', $model->rule_id)
                ->where('state', $model->state->value)
                ->where('time_logged', $model->time_logged)
                ->whereNotNull('fault_id');

            $sibling_count = $sibling_query()->count();

            if ($sibling_count > 1 && AlertUtil::shouldGroupFaultDetails($model->rule, $sibling_count)) {
                $entity_count = $sibling_count;

                // Only now (when grouping) load the detail blobs, chunked and stopping once
                // we have enough rows to fill the inline display cap.
                $rows = [];
                $sibling_query()
                    ->orderBy('id')
                    ->select(['id', 'details'])
                    ->chunk(200, function ($chunk) use (&$rows): bool {
                        foreach ($chunk as $sibling) {
                            foreach ((array) ($sibling->details['rule'] ?? []) as $row) {
                                $rows[] = $row;
                                if (count($rows) >= self::INLINE_DETAIL_ROW_LIMIT) {
                                    return false; // stop loading, we have enough to display
                                }
                            }
                        }

                        return true;
                    });
                $details = ['rule' => $rows] + $details;
            }
        }

        // Bound the rows handed to the parser so a single very large alert cannot exhaust
        // memory while rendering the collapsed inline detail. The full entity count is still
        // shown in the label, and complete details remain available via the details endpoint.
        if (isset($details['rule']) && is_array($details['rule']) && count($details['rule']) > self::INLINE_DETAIL_ROW_LIMIT) {
            $details['rule'] = array_slice($details['rule'], 0, self::INLINE_DETAIL_ROW_LIMIT);
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
