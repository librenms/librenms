<?php

namespace App\Models;

use App\Casts\CompressedJson;
use App\Facades\LibrenmsConfig;
use App\Models\Traits\AlertFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\AlertLogState;

class AlertLog extends DeviceRelatedModel
{
    use AlertFilters;
    use HasFactory;

    public const UPDATED_AT = null;
    public const CREATED_AT = 'time_logged';
    protected $table = 'alert_log';
    protected $fillable = [
        'device_id',
        'rule_id',
        'fault_id',
        'state',
        'time_logged',
        'details',
    ];

    /** @var list<string> */
    protected array $filterable = [
        'search',
        'state',
        'rule_id',
        'rule.severity',
        'device_id',
        'device.location_id',
        'device.groups.id',
    ];

    /**
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: array<string, string>, search?: bool}>
     */
    public static function filterFieldDefinitions(?int $deviceId = null): array
    {
        return self::alertFilterFields($deviceId);
    }

    /**
     * @return array<string, array{int, string}>
     */
    protected static function filterStates(): array
    {
        return [
            'ok' => [AlertLogState::Clear->value, __('Ok (recovered)')],
            'alerted' => [AlertLogState::Active->value, __('Alert')],
            'acknowledged' => [AlertLogState::Acknowledged->value, __('Acknowledged')],
            'worse' => [AlertLogState::Worse->value, __('Worse')],
            'better' => [AlertLogState::Better->value, __('Better')],
            'changed' => [AlertLogState::Changed->value, __('Changed')],
            'recovered' => [AlertLogState::Recovered->value, __('Recovered')],
        ];
    }

    /**
     * Fired alerts per day and severity, for the alert stats charts
     *
     * @param  Builder<AlertLog>  $query  alert log entries to count
     * @return array{groups: list<string>, items: list<array{x: string, y: int, group: string}>, zoom_max: int}
     */
    public static function severityStats(Builder $query): array
    {
        $dateFormat = LibrenmsConfig::get('alert_graph_date_format', '%Y-%m-%d');

        $stats = $query
            ->join('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id')
            ->where('alert_log.state', '!=', AlertLogState::Clear->value)
            ->selectRaw('DATE_FORMAT(alert_log.time_logged, ?) as date, COUNT(alert_log.rule_id) as total, alert_rules.severity', [$dateFormat])
            ->groupBy('date', 'alert_rules.severity')
            ->toBase()
            ->get();

        $items = $stats->map(fn ($row) => [
            'x' => (string) $row->date,
            'y' => (int) $row->total,
            'group' => (string) $row->severity,
        ])->values()->all();

        $firstDate = array_first($items)['x'] ?? null;
        $lastDate = array_last($items)['x'] ?? null;

        return [
            'groups' => array_values(array_unique(array_column($items, 'group'))),
            'items' => $items,
            'zoom_max' => ($firstDate && $lastDate) ? abs(strtotime($firstDate) - strtotime($lastDate)) * 1000 : 0,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state' => AlertLogState::class,
            'details' => CompressedJson::class,
            'time_logged' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AlertRule, $this>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'rule_id', 'id');
    }

    /**
     * @return BelongsTo<AlertFault, $this>
     */
    public function fault(): BelongsTo
    {
        return $this->belongsTo(AlertFault::class, 'fault_id', 'id');
    }
}
