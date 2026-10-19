<?php

namespace App\Models;

use App\Casts\CompressedJson;
use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Enum\AlertLogState;

class AlertLog extends DeviceRelatedModel
{
    use Filterable;
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
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: string[]|array<string, string>, search?: bool}>
     */
    public static function filterFieldDefinitions(): array
    {
        $user = auth()->user();

        $fields = [
            [
                'key' => 'search',
                'label' => __('Search'),
                'type' => 'text',
                'search' => true,
            ],
            [
                'key' => 'state',
                'label' => __('State'),
                'type' => 'select',
                'options' => [
                    AlertLogState::Clear->value => __('Ok (recovered)'),
                    AlertLogState::Active->value => __('Alert'),
                    AlertLogState::Acknowledged->value => __('Acknowledged'),
                    AlertLogState::Worse->value => __('Worse'),
                    AlertLogState::Better->value => __('Better'),
                    AlertLogState::Changed->value => __('Changed'),
                    AlertLogState::Recovered->value => __('Recovered'),
                ],
            ],
            [
                'key' => 'rule.severity',
                'label' => __('Severity'),
                'type' => 'select',
                'options' => [
                    'ok' => __('Ok'),
                    'warning' => __('Warning'),
                    'critical' => __('Critical'),
                ],
            ],
            [
                'key' => 'rule_id',
                'label' => __('Rule'),
                'type' => 'select',
                'endpoint' => route('ajax.select.alert-rule'),
            ],
            [
                'key' => 'device_id',
                'label' => __('Device'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device'),
            ],
        ];

        if ($user?->can('viewAny', Location::class)) {
            $fields[] = [
                'key' => 'device.location_id',
                'label' => __('Location'),
                'type' => 'select',
                'endpoint' => route('ajax.select.location'),
            ];
        }

        if ($user?->can('viewAny', DeviceGroup::class)) {
            $fields[] = [
                'key' => 'device.groups.id',
                'label' => __('device.device_group'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-group'),
            ];
        }

        return $fields;
    }

    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['rule.name', 'device.hostname', 'device.sysName', 'device.display'], $query, $value, $config);
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
