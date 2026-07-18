<?php

/**
 * AlertsController.php
 *
 * Controller for the active alerts bootgrid table.
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
 * @copyright  2026 LibreNMS Contributors
 */

namespace App\Http\Controllers\Table;

use App\Http\Parsers\AlertLogDetailParser;
use App\Models\Alert;
use App\Models\AlertLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Alert>
 */
class AlertsController extends TableController
{
    /** @var array<string, string> */
    protected array $default_sort = ['timestamp' => 'desc'];

    /** Maps the min_severity filter's UI values to alert_rules.severity comparisons. */
    private const SEVERITY_MAP = [
        'ok' => 1,
        'warning' => 2,
        'critical' => 3,
        'ok only' => 4,
        'warning only' => 5,
        'critical only' => 6,
    ];

    public function __construct(
        private readonly AlertLogDetailParser $parser
    ) {
    }

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'rule_id' => 'nullable|integer|min:1',
            'alert_id' => 'nullable|integer',
            'device_id' => 'nullable|integer',
            'acknowledged' => 'nullable|integer|in:0,1',
            'fired' => 'nullable|integer|in:0,1',
            'unreachable' => 'nullable|integer|in:0,1',
            'state' => 'nullable|integer',
            'min_severity' => 'nullable|string',
            'group' => 'nullable|integer|min:1',
            'uncollapse_key_count' => 'nullable|integer',
        ];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    protected function sortFields(Request $request): array
    {
        return [
            'timestamp' => 'timestamp',
            'severity' => ['alert_rules.severity', 'timestamp'],
            'rule' => 'alert_rules.name',
            'hostname' => 'devices.hostname',
            'location' => 'locations.location',
        ];
    }

    /**
     * @return array<int|string, string|array<int, string>>
     */
    protected function searchFields(Request $request): array
    {
        return [
            'alerts.timestamp',
            'rule' => ['builder', 'name'],
            'device' => ['hostname', 'sysName'],
        ];
    }

    /**
     * @return array<string, string|\Closure>
     */
    protected function filterFields(Request $request): array
    {
        return [
            'rule_id' => fn (Builder $q, ?int $id) => $id > 0 ? $q->where('alerts.rule_id', $id) : null,
            'alert_id' => fn (Builder $q, ?int $id) => $id > 0 ? $q->where('alerts.id', $id) : null,
            'device_id' => fn (Builder $q, ?int $id) => $id > 0 ? $q->where('alerts.device_id', $id) : null,

            'acknowledged' => function (Builder $q, ?string $acknowledged): void {
                if ($acknowledged !== null) {
                    if ((int) $acknowledged) {
                        $q->where('alerts.state', AlertState::ACKNOWLEDGED);
                    } else {
                        $q->where('alerts.state', '!=', AlertState::ACKNOWLEDGED);
                    }
                }
            },

            'fired' => function (Builder $q, ?string $fired): void {
                if ($fired) {
                    $q->where('alerts.state', AlertState::ACTIVE);
                }
            },

            'unreachable' => function (Builder $q, ?string $unreachable): void {
                if ($unreachable === null) {
                    return;
                }

                // A device is "unreachable" when it has at least one parent
                // relationship and none of its parents are up.
                $hasParent = fn ($query) => $query
                    ->from('device_relationships')
                    ->whereColumn('device_relationships.child_device_id', 'alerts.device_id');

                $hasUpParent = fn ($query) => $query
                    ->from('device_relationships')
                    ->join('devices as parent_devices', 'parent_devices.device_id', '=',
                        'device_relationships.parent_device_id')
                    ->whereColumn('device_relationships.child_device_id', 'alerts.device_id')
                    ->where('parent_devices.status', '!=', 0);

                if ((int) $unreachable) {
                    $q->whereExists($hasParent)->whereNotExists($hasUpParent);
                } else {
                    $q->where(fn ($query) => $query->whereNotExists($hasParent)->orWhereExists($hasUpParent));
                }
            },

            'state' => function (Builder $q, ?string $state): void {
                if ($state !== null) {
                    $q->where('alerts.state', (int) $state);
                }
            },

            'min_severity' => function (Builder $q, ?string $minSeverity): void {
                if (! $minSeverity) {
                    return;
                }

                $severityId = is_numeric($minSeverity)
                    ? (int) $minSeverity
                    : (self::SEVERITY_MAP[$minSeverity] ?? null);

                if ($severityId === null) {
                    return;
                }

                // Values 1-3 mean "X or higher"; values 4-6 mean exact match ("X only")
                $q->whereHas('rule', function (Builder $rq) use ($severityId): void {
                    if ($severityId > 3) {
                        $rq->where('severity', $severityId - 3);
                    } else {
                        $rq->where('severity', '>=', $severityId);
                    }
                });
            },

            'group' => function ($q, ?int $group): void {
                /** @var Builder<Alert> $q */
                if ($group) {
                    $q->inDeviceGroup($group);
                }
            },
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Alert::class);

        // Correlated sub-select: resolves the latest alert_log.id for each alert's
        // (rule_id, device_id) pair. Runs as a single column in the main SELECT.
        // The actual AlertLog rows are then batch-loaded in formatResponse.
        $latestLogIdSub = DB::table('alert_log')
            ->selectRaw('MAX(id)')
            ->whereColumn('alert_log.rule_id', 'alerts.rule_id')
            ->whereColumn('alert_log.device_id', 'alerts.device_id');

        $query = Alert::query()
            ->select('alerts.*')
            ->selectSub($latestLogIdSub, 'latest_alert_log_id')
            ->with(['device', 'device.location', 'rule', 'latestLog'])
            ->whereHas('device', fn (Builder $q) => $q->where('disabled', 0))
            ->hasAccess($request->user());

        // By default, hide recovered alerts unless state=0 is explicitly requested
        $stateFilter = $request->input('state');
        if ($stateFilter === null || (int) $stateFilter !== AlertState::RECOVERED) {
            $query->where('alerts.state', '!=', AlertState::RECOVERED);
        }

        // Add joins only for the sort columns that actually need them
        $sort = $request->input('sort', []);
        if (isset($sort['severity']) || isset($sort['rule'])) {
            $query->leftJoin('alert_rules', 'alerts.rule_id', '=', 'alert_rules.id');
        }
        if (isset($sort['hostname'])) {
            $query->leftJoin('devices', 'alerts.device_id', '=', 'devices.device_id');
        }
        if (isset($sort['location'])) {
            if (! isset($sort['hostname'])) {
                $query->leftJoin('devices', 'alerts.device_id', '=', 'devices.device_id');
            }
            $query->leftJoin('locations', 'devices.location_id', '=', 'locations.id');
        }

        return $query;
    }

    /**
     * Format an alert row for the bootgrid table response.
     *
     * @param  Alert  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        [$faultDetail, $maxRowLength] = $this->renderFaultDetail($model->latestLog);

        $state = (int) $model->state;
        $collapseClass = $this->incidentCollapseClass($model->getAttribute('uncollapse_key_count'), $maxRowLength);

        $hostname = '<div class="incident">'
            . Url::modernDeviceLink($model->device)
            . '<div id="incident' . $model->id . '"' . $collapseClass . '>' . $faultDetail . '</div>'
            . '</div>';

        $noteClass = empty($model->note) ? 'default' : 'warning';

        $location = $model->device?->location?->location;

        return [
            'rule' => '<i title="' . e(json_encode($model->rule?->builder)) . '"><a href="' . Url::generate(['page' => 'alert-rules']) . '">' . e((string) $model->rule?->name) . '</a></i>',
            'details' => '<a class="fa-solid fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $model->id . '" data-parent="#alerts"></a>',
            'verbose_details' => $this->verboseDetailsButton($model->latestLog?->id),
            'hostname' => $hostname,
            'location' => '<a href="' . e(Url::generate(['page' => 'devices', 'location' => $location ?? ''])) . '">' . e($location ?? 'N/A') . '</a>',
            'timestamp' => $model->timestamp ? Time::format($model->timestamp, 'compact') : 'N/A',
            'severity' => $this->severityIcon($model->rule?->severity, $state),
            'state' => $state,
            'alert_id' => $model->id,
            'ack_ico' => $this->ackButton($model, $state),
            'proc' => $this->procButton($model->rule?->proc),
            'notes' => "<button type='button' class='btn btn-{$noteClass} fa fa-sticky-note-o command-alert-note' aria-label='Notes' id='alert-notes' data-alert_id='{$model->id}'></button>",
        ];
    }

    /**
     * Render the collapsible fault detail HTML from an eager-loaded latestLog entry.
     *
     * @return array{0: string, 1: int} [html, plain-text length]
     */
    private function renderFaultDetail(?AlertLog $latestLog): array
    {
        if (! $latestLog || empty($latestLog->details)) {
            return ['', 0];
        }

        $html = view('alerts.fault-detail', [
            'details' => $this->parser->parse($latestLog->details),
        ])->render();

        return [$html, strlen(strip_tags($html))];
    }

    private function incidentCollapseClass(mixed $uncollapseKeyCount, int $maxRowLength): string
    {
        return (is_numeric($uncollapseKeyCount) && $maxRowLength < (int) $uncollapseKeyCount)
            ? ''
            : ' class="collapse"';
    }

    private function severityIcon(?string $severity, int $state): string
    {
        if ($state === AlertState::ACKNOWLEDGED) {
            return '<span class="alert-status label-primary">&nbsp;</span>';
        }

        $color = match ($severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            'ok' => 'success',
            default => 'info',
        };

        return '<span class="alert-status label-' . $color . '">&nbsp;</span>';
    }

    private function ackButton(Alert $model, int $state): string
    {
        if (! Gate::allows('alert.update')) {
            return '';
        }

        $info = is_array($model->info) ? $model->info : [];
        $btnBase = "type=\"button\" data-target=\"ack-alert\" data-state=\"{$state}\" data-alert_id=\"{$model->id}\" data-alert_state=\"{$state}\" name=\"ack-alert\"";

        if ($state !== AlertState::ACKNOWLEDGED) {
            return "<button {$btnBase} class=\"btn btn-danger command-ack-alert fa fa-eye\" aria-hidden=\"true\" title=\"Mark as acknowledged\"></button>";
        }

        // Acknowledged-until-clear vs. standard ack both offer an un-ack action,
        // just with different icons for clarity.
        $icon = ($info['until_clear'] ?? true) === false ? 'fa-eye' : 'fa-eye-slash';

        return "<button {$btnBase} class=\"btn btn-primary command-ack-alert fa {$icon}\" aria-hidden=\"true\" title=\"Mark as not acknowledged\"></button>";
    }

    private function procButton(?string $proc): string
    {
        if (! $proc || $proc === 'NULL' || ! preg_match('#^https?://#', $proc)) {
            return '';
        }

        return '<a href="' . e($proc) . '" target="_blank"><button type="button" class="btn btn-info fa fa-external-link" aria-hidden="true"></button></a>';
    }

    private function verboseDetailsButton(?int $alertLogId): string
    {
        if (! Gate::allows('alert.detail')) {
            return '';
        }

        return "<button type='button' class='btn btn-alert-details command-alert-details' aria-label='Details' id='alert-details' data-alert_log_id='{$alertLogId}'><i class='fa-solid fa-circle-info'></i></button>";
    }
}
