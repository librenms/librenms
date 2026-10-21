<?php

namespace App\Models;

use App\Casts\CompressedJson;
use App\Models\Traits\AlertFilters;
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
    public static function filterFieldDefinitions(): array
    {
        return self::alertFilterFields();
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
