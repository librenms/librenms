<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LibreNMS\Enum\SensorClass;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Number;

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

    // ---- Helper Methods ----

    public function classDescr(): string
    {
        return SensorClass::descr($this->sensor_class);
    }

    public function classDescrLong(): string
    {
        return SensorClass::descrLong($this->sensor_class);
    }

    public function unit(): string
    {
        return SensorClass::unit($this->sensor_class);
    }

    public function unitLong(): string
    {
        return SensorClass::unitLong($this->sensor_class);
    }

    public function icon()
    {
        return SensorClass::icon($this->sensor_class);
    }

    public function guessLimits(bool $high, bool $high_warn, bool $low_warn, bool $low): void
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

        if ($high_warn) {
            $this->sensor_limit_warn = match ($this->sensor_class) {
                default => null,
            };
        }

        if ($low_warn) {
            $this->sensor_limit_low_warn = match ($this->sensor_class) {
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
        return match ($this->sensor_class) {
            'current', 'power' => Number::formatSi($this->sensor_current, 3, 0, $this->unit()),
            'dbm' => round($this->sensor_current, 3) . ' ' . $this->unit(),
            default => $this->sensor_current . ' ' . $this->unit(),
        };
    }

    public function currentStatus(): Severity
    {
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
