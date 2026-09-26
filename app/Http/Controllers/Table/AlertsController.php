<?php

/**
 * AlertsController.php
 *
 * Controller for the active faults bootgrid table.
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
use App\Models\AlertFault;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

/**
 * @extends TableController<AlertFault>
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
            'alert_faults.timestamp',
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
            'rule_id' => fn (Builder $q, ?int $id) => $id > 0 ? $q->where('alert_faults.rule_id', $id) : null,
            'alert_id' => fn (Builder $q, ?int $id) => $id > 0 ? $q->where('alert_faults.id', $id) : null,
            'device_id' => fn (Builder $q, ?int $id) => $id > 0 ? $q->where('alert_faults.device_id', $id) : null,

            'acknowledged' => function (Builder $q, ?string $acknowledged): void {
                if ($acknowledged !== null) {
                    if ((int) $acknowledged) {
                        $q->where('alert_faults.state', AlertState::ACKNOWLEDGED);
                    } else {
                        $q->where('alert_faults.state', '!=', AlertState::ACKNOWLEDGED);
                    }
                }
            },

            'fired' => function (Builder $q, ?string $fired): void {
                if ($fired) {
                    $q->where('alert_faults.state', AlertState::ACTIVE);
                }
            },

            'unreachable' => function (Builder $q, ?string $unreachable): void {
                if ($unreachable === null) {
                    return;
                }

                $hasParent = fn ($query) => $query
                    ->from('device_relationships')
                    ->whereColumn('device_relationships.child_device_id', 'alert_faults.device_id');

                $hasUpParent = fn ($query) => $query
                    ->from('device_relationships')
                    ->join('devices as parent_devices', 'parent_devices.device_id', '=',
                        'device_relationships.parent_device_id')
                    ->whereColumn('device_relationships.child_device_id', 'alert_faults.device_id')
                    ->where('parent_devices.status', '!=', 0);

                if ((int) $unreachable) {
                    $q->whereExists($hasParent)->whereNotExists($hasUpParent);
                } else {
                    $q->where(fn ($query) => $query->whereNotExists($hasParent)->orWhereExists($hasUpParent));
                }
            },

            'state' => function (Builder $q, ?string $state): void {
                if ($state !== null) {
                    $q->where('alert_faults.state', (int) $state);
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

                $q->whereHas('rule', function (Builder $rq) use ($severityId): void {
                    if ($severityId > 3) {
                        $rq->where('severity', $severityId - 3);
                    } else {
                        $rq->where('severity', '>=', $severityId);
                    }
                });
            },

            'group' => function ($q, ?int $group): void {
                /** @var Builder<AlertFault> $q */
                if ($group) {
                    $q->inDeviceGroup($group);
                }
            },
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Alert::class);

        $query = AlertFault::query()
            ->select('alert_faults.*')
            ->with(['device', 'device.location', 'rule'])
            ->where('alert_faults.open', 1)
            ->whereHas('device', fn (Builder $q) => $q->where('disabled', 0))
            ->whereHas('rule', fn (Builder $q) => $q->where('disabled', 0))
            ->hasAccess($request->user());

        $stateFilter = $request->input('state');
        if ($stateFilter === null || (int) $stateFilter !== AlertState::RECOVERED) {
            $query->where('alert_faults.state', '!=', AlertState::RECOVERED);
        }

        // Per-entity rules show every fault; grouped rules collapse to one row per device+rule.
        $query->whereRaw('(
            coalesce((select ar.notify_per_entity from alert_rules ar where ar.id = alert_faults.rule_id), 0) = 1
            or alert_faults.id = (
                select min(f2.id) from alert_faults f2
                where f2.device_id = alert_faults.device_id
                  and f2.rule_id = alert_faults.rule_id
                  and f2.open = 1
                  and f2.state != ?
            )
        )', [AlertState::RECOVERED]);

        $sort = $request->input('sort', []);
        if (isset($sort['severity']) || isset($sort['rule'])) {
            $query->leftJoin('alert_rules', 'alert_faults.rule_id', '=', 'alert_rules.id');
        }
        if (isset($sort['hostname'])) {
            $query->leftJoin('devices', 'alert_faults.device_id', '=', 'devices.device_id');
        }
        if (isset($sort['location'])) {
            if (! isset($sort['hostname'])) {
                $query->leftJoin('devices', 'alert_faults.device_id', '=', 'devices.device_id');
            }
            $query->leftJoin('locations', 'devices.location_id', '=', 'locations.id');
        }

        return $query;
    }

    /**
     * @param  AlertFault  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $state = (int) $model->state;
        $noteClass = empty($model->note) ? 'default' : 'warning';
        $location = $model->device?->location?->location;
        $entityCount = $this->entityCount($model);
        $ruleName = e((string) $model->rule?->name);
        if ($entityCount > 1) {
            $ruleName .= ' <span class="label label-default" title="' . $entityCount . ' matching entities grouped into this alert">' . $entityCount . '&times;</span>';
        }

        $alertLogId = AlertLog::query()->where('fault_id', $model->id)->max('id');

        return [
            'rule' => '<i title="' . e(json_encode($model->rule?->builder)) . '"><a href="' . Url::generate(['page' => 'alert-rules']) . '">' . $ruleName . '</a></i>',
            'details' => '<a class="fa-solid fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $model->id . '" data-parent="#alerts"></a>',
            'verbose_details' => $this->verboseDetailsButton($alertLogId),
            'hostname' => $this->renderHostname($model),
            'location' => '<a href="' . e(Url::generate(['page' => 'devices', 'location' => $location ?? ''])) . '">' . e($location ?? 'N/A') . '</a>',
            'timestamp' => $model->timestamp ? Time::format($model->timestamp, 'compact') : 'N/A',
            'severity' => $this->severityIcon($model->rule?->severity, $state),
            'state' => $state,
            'alert_id' => $model->id,
            'ack_ico' => $this->ackButton($model, $state),
            'proc' => $this->procButton($model->rule?->proc),
            'notes' => "<button type='button' class='btn btn-$noteClass fa fa-sticky-note-o command-alert-note' aria-label='Notes' id='alert-notes' data-alert_id='$model->id'></button>",
        ];
    }

    private function entityCount(AlertFault $fault): int
    {
        if ($fault->rule?->notify_per_entity) {
            return 1;
        }

        return AlertFault::query()
            ->where('device_id', $fault->device_id)
            ->where('rule_id', $fault->rule_id)
            ->where('open', 1)
            ->where('state', '!=', AlertState::RECOVERED)
            ->count();
    }

    /**
     * @return array<string, mixed>
     */
    private function faultDetails(AlertFault $fault): array
    {
        $details = is_array($fault->details) ? $fault->details : [];

        if ($fault->rule && ! $fault->rule->notify_per_entity) {
            $rows = [];
            $siblings = AlertFault::query()
                ->where('device_id', $fault->device_id)
                ->where('rule_id', $fault->rule_id)
                ->where('open', 1)
                ->where('state', '!=', AlertState::RECOVERED)
                ->get(['id', 'details']);
            foreach ($siblings as $sibling) {
                foreach ((array) ($sibling->details['rule'] ?? []) as $row) {
                    $rows[] = $row;
                }
            }
            if (! empty($rows)) {
                $details = ['rule' => $rows] + $details;
            }
        }

        return $details;
    }

    private function renderFaultDetail(AlertFault $fault): string
    {
        $details = $this->faultDetails($fault);
        if (empty($details)) {
            return '';
        }

        return view('alerts.fault-detail', [
            'details' => $this->parser->parse($details),
        ])->render();
    }

    private function renderHostname(AlertFault $fault): string
    {
        $faultDetail = $this->renderFaultDetail($fault);
        $collapseClass = $this->incidentCollapseClass($faultDetail);

        return '<div class="incident">'
            . Url::modernDeviceLink($fault->device)
            . '<div id="incident' . $fault->id . '"' . $collapseClass . '>' . $faultDetail . '</div>'
            . '</div>';
    }

    private function countPrintableCharacters(string $string): int
    {
        $string = trim(strip_tags($string));
        $string = preg_replace('/[[:^print:]]\s/u', '', $string);
        $string = preg_replace('/ +/', ' ', $string);

        return strlen($string);
    }

    private function incidentCollapseClass(string $detail): string
    {
        if (empty($detail)) {
            return '';
        }

        $uncollapseKeyCount = request()->input('uncollapse_key_count');
        $maxRowLength = $this->countPrintableCharacters($detail);

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

        $icon = '<span class="alert-status label-' . $color . '">&nbsp;</span>';
        if ($state === AlertState::WORSE) {
            $icon .= ' <strong>+</strong>';
        } elseif ($state === AlertState::BETTER) {
            $icon .= ' <strong>-</strong>';
        }

        return $icon;
    }

    private function ackButton(AlertFault $model, int $state): string
    {
        if (! Gate::allows('alert.update')) {
            return '';
        }

        $info = is_array($model->info) ? $model->info : [];
        $btnBase = "type=\"button\" data-target=\"ack-alert\" data-state=\"$state\" data-alert_id=\"{$model->id}\" data-alert_state=\"$state\" name=\"ack-alert\"";

        if ($state !== AlertState::ACKNOWLEDGED) {
            return "<button $btnBase class=\"btn btn-danger command-ack-alert fa fa-eye\" aria-hidden=\"true\" title=\"Mark as acknowledged\"></button>";
        }

        $icon = ($info['until_clear'] ?? true) === false ? 'fa-eye' : 'fa-eye-slash';

        return "<button $btnBase class=\"btn btn-primary command-ack-alert fa $icon\" aria-hidden=\"true\" title=\"Mark as not acknowledged\"></button>";
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

        return "<button type='button' class='btn btn-alert-details command-alert-details' aria-label='Details' id='alert-details' data-alert_log_id='$alertLogId'><i class='fa-solid fa-circle-info'></i></button>";
    }
}
