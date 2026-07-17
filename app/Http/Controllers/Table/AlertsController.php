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

    protected function rules(): array
    {
        return [
            'rule_id' => 'nullable|integer|min:1',
            'alert_id' => 'nullable|integer|min:1',
            'device_id' => 'nullable|integer|min:1',
            'acknowledged' => 'nullable|integer|in:0,1',
            'fired' => 'nullable|integer|in:0,1',
            'unreachable' => 'nullable|integer|in:0,1',
            'state' => 'nullable|integer',
            'min_severity' => 'nullable|string',
            'group' => 'nullable|integer|min:1',
            'uncollapse_key_count' => 'nullable|integer',
        ];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'timestamp',
            'severity' => ['alert_rules.severity', 'timestamp'],
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'alerts.timestamp',
            'alert_rules.builder',
            'alert_rules.name',
            'devices.hostname',
            'devices.sysName',
        ];
    }

    protected function filterFields(Request $request): array
    {
        return [
            'alerts.rule_id' => 'rule_id',
            'alerts.id' => 'alert_id',
            'alerts.device_id' => 'device_id',

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
                $hasParent = fn (Builder $query) => $query
                    ->from('device_relationships')
                    ->whereColumn('device_relationships.child_device_id', 'devices.device_id');

                $hasUpParent = fn (Builder $query) => $query
                    ->from('device_relationships')
                    ->join('devices as parent_devices', 'parent_devices.device_id', '=', 'device_relationships.parent_device_id')
                    ->whereColumn('device_relationships.child_device_id', 'devices.device_id')
                    ->where('parent_devices.status', '!=', 0);

                if ((int) $unreachable) {
                    $q->whereExists($hasParent)->whereNotExists($hasUpParent);
                } else {
                    $q->where(fn (Builder $query) => $query->whereNotExists($hasParent)->orWhereExists($hasUpParent));
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
                if ($severityId > 3) {
                    $q->where('alert_rules.severity', $severityId - 3);
                } else {
                    $q->where('alert_rules.severity', '>=', $severityId);
                }
            },

            'group' => function (Builder $q, ?int $group): void {
                if ($group) {
                    $q->inDeviceGroup($group);
                }
            },
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Alert::class);

        // Sub-select to fetch the latest alert_log id per (rule_id, device_id) without N+1
        $latestLogIdSub = DB::table('alert_log')
            ->selectRaw('MAX(id)')
            ->whereColumn('alert_log.rule_id', 'alerts.rule_id')
            ->whereColumn('alert_log.device_id', 'alerts.device_id');

        $query = Alert::query()
            ->select('alerts.*')
            ->selectSub($latestLogIdSub, 'latest_alert_log_id')
            ->leftJoin('devices', 'alerts.device_id', '=', 'devices.device_id')
            ->leftJoin('locations', 'devices.location_id', '=', 'locations.id')
            ->rightJoin('alert_rules', 'alerts.rule_id', '=', 'alert_rules.id')
            ->addSelect([
                'devices.hostname',
                'devices.sysName',
                'devices.display',
                'devices.os',
                'devices.hardware',
                'locations.location',
                'alert_rules.name',
                'alert_rules.severity',
                'alert_rules.builder',
                'alert_rules.proc',
                'alerts.note',
            ])
            ->where('devices.disabled', 0)
            ->hasAccess($request->user());

        // By default, hide recovered alerts unless state=0 is explicitly requested
        $stateFilter = $request->input('state');
        if ($stateFilter === null || (int) $stateFilter !== AlertState::RECOVERED) {
            $query->where('alerts.state', '!=', AlertState::RECOVERED);
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
        [$faultDetail, $maxRowLength] = $this->renderFaultDetail($model->latest_alert_log_id);

        $state = (int) $model->state;
        $collapseClass = $this->incidentCollapseClass($model->getAttribute('uncollapse_key_count'), $maxRowLength);

        $hostname = '<div class="incident">'
            . Url::modernDeviceLink($model->device)
            . '<div id="incident' . $model->id . '"' . $collapseClass . '>' . $faultDetail . '</div>'
            . '</div>';

        $noteClass = empty($model->note) ? 'default' : 'warning';

        return [
            'rule' => '<i title="' . htmlentities((string) $model->builder) . '"><a href="' . Url::generate(['page' => 'alert-rules']) . '">' . htmlentities((string) $model->name) . '</a></i>',
            'details' => '<a class="fa-solid fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $model->id . '" data-parent="#alerts"></a>',
            'verbose_details' => $this->verboseDetailsButton($model->latest_alert_log_id),
            'hostname' => $hostname,
            'location' => generate_link(htmlspecialchars($model->location ?? 'N/A'),
                ['page' => 'devices', 'location' => $model->location ?? '']),
            'timestamp' => $model->timestamp ? Time::format($model->timestamp, 'compact') : 'N/A',
            'severity' => $this->severityIcon($model->severity, $state),
            'state' => $state,
            'alert_id' => $model->id,
            'ack_ico' => $this->ackButton($model, $state),
            'proc' => $this->procButton($model->proc),
            'notes' => "<button type='button' class='btn btn-{$noteClass} fa fa-sticky-note-o command-alert-note' aria-label='Notes' id='alert-notes' data-alert_id='{$model->id}'></button>",
        ];
    }

    /**
     * Render the collapsible fault detail HTML for the latest alert_log entry.
     *
     * @return array{0: string, 1: int} [html, plain-text length]
     */
    private function renderFaultDetail(?int $alertLogId): array
    {
        if (! $alertLogId) {
            return ['', 0];
        }

        $rawLog = DB::table('alert_log')->where('id', $alertLogId)->value('details');
        if (! $rawLog) {
            return ['', 0];
        }

        $details = json_decode(gzuncompress($rawLog), true) ?? [];
        $html = view('alerts.fault-detail', ['details' => $this->parser->parse($details)])->render();

        return [$html, strlen(strip_tags($html))];
    }

    private function incidentCollapseClass(mixed $uncollapseKeyCount, int $maxRowLength): string
    {
        return (is_numeric($uncollapseKeyCount) && $maxRowLength < (int) $uncollapseKeyCount)
            ? ''
            : ' class="collapse"';
    }

    private function severityIcon(string $severity, int $state): string
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

        return '<a href="' . htmlspecialchars($proc) . '" target="_blank"><button type="button" class="btn btn-info fa fa-external-link" aria-hidden="true"></button></a>';
    }

    private function verboseDetailsButton(?int $alertLogId): string
    {
        if (! Gate::allows('alert.detail')) {
            return '';
        }

        return "<button type='button' class='btn btn-alert-details command-alert-details' aria-label='Details' id='alert-details' data-alert_log_id='{$alertLogId}'><i class='fa-solid fa-circle-info'></i></button>";
    }
}
