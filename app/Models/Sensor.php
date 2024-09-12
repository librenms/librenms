<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LibreNMS\Interfaces\Models\Keyable;

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

    // ---- Helper Functions ----

    public function classDescr()
    {
        $nice = collect([
            'ber' => 'BER',
            'dbm' => 'dBm',
            'eer' => 'EER',
            'snr' => 'SNR',
        ]);

        return $nice->get($this->sensor_class, ucwords(str_replace('_', ' ', $this->sensor_class)));
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

    public function guessLimits(): void
    {
        $this->sensor_limit = match ($this->sensor_class) {
            'temperature' => $this->sensor_current - 10,
            'voltage' => $this->sensor_current * 0.85,
            'humidity' => 30,
            'fanspeed' => $this->sensor_current * 0.80,
            'power_factor' => -1,
            'signal' => -80,
            'airflow', 'snr', 'frequency', 'pressure', 'cooling' => $this->sensor_current * 0.95,
            default => null,
        };

        $this->sensor_limit_low = match ($this->sensor_class) {
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

    // ---- Define Relationships ----
    public function events(): MorphMany
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }

    public function translations(): BelongsToMany
    {
        return $this->belongsToMany(StateTranslation::class, 'sensors_to_state_indexes', 'sensor_id', 'state_index_id');
    }

    public function getCompositeKey(): string
    {
        return "$this->poller_type-$this->sensor_class-$this->device_id-$this->sensor_type-$this->sensor_index";
    }

    public function syncGroup(): string
    {
        return "$this->sensor_class-$this->poller_type";
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
        $data[] = "rrd_type = $this->sensor_rrd_type";

        return implode(', ', $data);
    }
}
