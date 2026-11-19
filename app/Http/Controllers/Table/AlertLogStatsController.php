<?php

namespace App\Http\Controllers\Table;

use App\Facades\Permissions;
use App\Models\Alert;
use App\Models\AlertLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\AlertLogState;
use LibreNMS\Util\Url;

/**
 * @extends TableController<AlertLog>
 */
class AlertLogStatsController extends TableController
{
    private const array SEVERITIES = [
        'ok' => ['ok', 'warning', 'critical'],
        'warning' => ['warning', 'critical'],
        'critical' => ['critical'],
        'ok only' => ['ok'],
        'warning only' => ['warning'],
        'critical only' => ['critical'],
    ];

    /**
     * @return array<string, string|list<string|\Illuminate\Validation\Rules\In>>
     */
    protected function rules(): array
    {
        return [
            'device_id' => 'nullable|integer',
            'min_severity' => ['nullable', Rule::in([1, 2, 3, 4, 5, 6, ...array_keys(self::SEVERITIES)])],
            'sort' => 'array',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $groups = AlertLog::query()
            ->leftJoin('devices', 'alert_log.device_id', '=', 'devices.device_id')
            ->join('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id')
            ->where('alert_log.state', AlertLogState::Active->value)
            ->select('devices.device_id', 'alert_rules.name')
            ->selectRaw('MIN(alert_log.id) as id, COUNT(*) as event_count')
            ->groupBy('devices.device_id', 'alert_rules.name');

        $user = $request->user();
        if (! $user->can('viewAll', Alert::class)) {
            $groups->whereIntegerInRaw('alert_log.device_id', Permissions::devicesForUser($user));
        }

        $days = $request->input('time_interval');
        if (is_numeric($days)) {
            $groups->whereRaw('alert_log.time_logged > DATE_SUB(NOW(), INTERVAL ? DAY)', [$days]);
        }

        if ($request->filled('min_severity')) {
            $severity = $request->input('min_severity');
            if (is_numeric($severity)) {
                $severity = array_keys(self::SEVERITIES)[(int) $severity - 1];
            }
            $groups->whereIn('alert_rules.severity', self::SEVERITIES[$severity]);
        }

        $this->filter($request, $groups, ['alert_log.device_id' => 'device_id']);
        $this->search($request->input('searchPhrase'), $groups, ['devices.hostname', 'devices.sysName', 'alert_log.time_logged', 'alert_rules.name']);

        // Paginate the grouped rows so the total and the All option use the same result set.
        return AlertLog::query()
            ->fromSub($groups, 'alert_log')
            ->with('device')
            ->orderByDesc('event_count')
            ->orderBy('device_id')
            ->orderBy('name');
    }

    /**
     * @param  AlertLog  $model
     */
    public function formatItem(Model $model): array
    {
        return [
            'id' => $model->id,
            'count' => (int) $model->getAttribute('event_count'),
            'hostname' => '<div class="incident">' . Url::deviceLink($model->device) . '</div>',
            'alert_rule' => e($model->getAttribute('name')),
        ];
    }
}
