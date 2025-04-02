<?php

namespace App\Models;

use App\Facades\LibrenmsConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Number;
use LibreNMS\Util\Time;

class Sensor extends DeviceRelatedModel implements Keyable
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'sensor_id';
    protected $fillable = [
        'poller_type',
        'sensor_class',
        'device_id',
        'sensor_oid',
        'sensor_index',
        'sensor_type',
        'sensor_descr',
        'sensor_divisor',
        'sensor_multiplier',
        'sensor_limit',
        'sensor_limit_warn',
        'sensor_limit_low',
        'sensor_limit_low_warn',
        'sensor_current',
        'entPhysicalIndex',
        'entPhysicalIndex_measured',
        'user_func',
        'group',
        'rrd_type',
    ];
    protected static $icons = [
        'airflow' => 'angle-double-right',
        'ber' => 'sort-amount-desc',
        'charge' => 'battery-half',
        'chromatic_dispersion' => 'indent',
        'cooling' => 'thermometer-full',
        'count' => 'hashtag',
        'current' => 'bolt fa-flip-horizontal',
        'dbm' => 'sun-o',
        'delay' => 'clock-o',
        'eer' => 'snowflake-o',
        'fanspeed' => 'refresh',
        'frequency' => 'line-chart',
        'humidity' => 'tint',
        'load' => 'percent',
        'loss' => 'percentage',
        'power' => 'power-off',
        'power_consumed' => 'plug',
        'power_factor' => 'calculator',
        'pressure' => 'thermometer-empty',
        'quality_factor' => 'arrows',
        'runtime' => 'hourglass-half',
        'signal' => 'wifi',
        'snr' => 'signal',
        'state' => 'bullseye',
        'temperature' => 'thermometer-three-quarters',
        'tv_signal' => 'signal',
        'bitrate' => 'bar-chart',
        'voltage' => 'bolt',
        'waterflow' => 'tint',
        'percent' => 'percent',
    ];

    // ---- Helper Methods ----

    public function classDescr(): string
    {
        return __('sensors.' . $this->sensor_class . '.short');
    }

    public function classDescrLong(): string
    {
        return __('sensors.' . $this->sensor_class . '.long');
    }

    public function unit(): string
    {
        return __('sensors.' . $this->sensor_class . '.unit');
    }

    public function unitLong(): string
    {
        return __('sensors.' . $this->sensor_class . '.unit_long');
    }

    public function icon()
    {
        return collect(self::$icons)->get($this->sensor_class, 'delicius');
    }

    public static function getTypes()
    {
        return array_keys(self::$icons);
    }

    // for the legacy menu
    public static function getIconMap()
    {
        return self::$icons;
    }

    public function guessLimits(bool $high, bool $low): void
    {
        if ($high) {
            $this->sensor_limit = match ($this->sensor_class) {
                'temperature' => $this->sensor_current + 20,
                'voltage' => $this->sensor_current * 1.15,
                'humidity' => 70,
                'fanspeed' => $this->sensor_current * 1.80,
                'power_factor' => 1,
                'signal' => -30,
                'load' => 80,
                'airflow', 'snr', 'frequency', 'pressure', 'cooling' => $this->sensor_current * 1.05,
                default => null,
            };
        }

        if ($low) {
            $this->sensor_limit_low = match ($this->sensor_class) {
                'temperature' => $this->sensor_current - 10,
                'voltage' => $this->sensor_current * 0.85,
                'humidity' => 30,
                'fanspeed' => $this->sensor_current * 0.80,
                'power_factor' => -1,
                'signal' => -80,
                'airflow', 'snr', 'frequency', 'pressure', 'cooling' => $this->sensor_current * 0.95,
                default => null,
            };
        }
    }

    /**
     * Format current value for user display including units.
     */
    public function formatValue(): string
    {
        $value = $this->sensor_current;
        if (in_array($this->rrd_type, ['COUNTER', 'DERIVE', 'DCOUNTER', 'DDERIVE'])) {
            //compute and display an approx rate for this sensor
            $value = Number::formatSi(max(0, $value - $this->sensor_prev) / LibrenmsConfig::get('rrd.step', 300), 2, 3, '');
        }

        return match ($this->sensor_class) {
            'state' => $this->currentTranslation()?->state_descr ?? 'Unknown',
            'current', 'power' => Number::formatSi($value, 3, 0, $this->unit()),
            'runtime' => Time::formatInterval($value * 60),
            'power_consumed' => trim(Number::formatSi($value * 1000, 5, 5, 'Wh')),
            'dbm' => round($value, 3) . ' ' . $this->unit(),
            default => $value . ' ' . $this->unit(),
        };
    }

    public function currentTranslation(): ?StateTranslation
    {
        if ($this->sensor_class !== 'state') {
            return null;
        }

        return $this->translations->firstWhere('state_value', $this->sensor_current);
    }

    public function currentStatus(): Severity
    {
        if ($this->sensor_class == 'state') {
            return $this->currentTranslation()?->severity() ?? Severity::Unknown;
        }

        if ($this->sensor_limit !== null && $this->sensor_current >= $this->sensor_limit) {
            return Severity::Error;
        }
        if ($this->sensor_limit_low !== null && $this->sensor_current <= $this->sensor_limit_low) {
            return Severity::Error;
        }

        if ($this->sensor_limit_warn !== null && $this->sensor_current >= $this->sensor_limit_warn) {
            return Severity::Warning;
        }

        if ($this->sensor_limit_low_warn !== null && $this->sensor_current <= $this->sensor_limit_low_warn) {
            return Severity::Warning;
        }

        return Severity::Ok;
    }

    public function hasThresholds(): bool
    {
        return $this->sensor_limit_low !== null
            || $this->sensor_limit_low_warn !== null
            || $this->sensor_limit_warn !== null
            || $this->sensor_limit !== null;
    }

    public function doesntHaveThresholds(): bool
    {
        return ! $this->hasThresholds();
    }
    // ---- Define Relationships ----

    public function events(): MorphMany
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }

    public function stateIndex(): HasOneThrough
    {
        return $this->hasOneThrough(StateIndex::class, SensorToStateIndex::class, 'sensor_id', 'state_index_id', 'sensor_id', 'state_index_id');
    }

    public function translations(): BelongsToMany
    {
        return $this->belongsToMany(StateTranslation::class, 'sensors_to_state_indexes', 'sensor_id', 'state_index_id', 'sensor_id', 'state_index_id');
    }

    public function getCompositeKey(): string
    {
        return "$this->poller_type-$this->sensor_class-$this->device_id-$this->sensor_type-$this->sensor_index";
    }

    public function syncGroup(): string
    {
        return "$this->sensor_class-$this->poller_type";
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsCritical($query)
    {
        return $query->whereColumn('sensor_current', '<', 'sensor_limit_low')
            ->orWhereColumn('sensor_current', '>', 'sensor_limit');
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where('sensor_alert', 0);
    }

    public function __toString()
    {
        $data = $this->only([
            'sensor_oid',
            'sensor_index',
            'sensor_type',
            'sensor_descr',
            'poller_type',
            'sensor_divisor',
            'sensor_multiplier',
            'entPhysicalIndex',
            'sensor_current',
        ]);
        $data[] = "(limits: LL: $this->sensor_limit_low, LW: $this->sensor_limit_low_warn, W: $this->sensor_limit_warn, H: $this->sensor_limit)";
        $data[] = "rrd_type = $this->rrd_type";

        return implode(', ', $data);
    }
}
