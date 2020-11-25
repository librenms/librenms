<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sensor extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'sensor_id';
    protected static $icons = [
        'airflow'              => 'angle-double-right',
        'ber'                  => 'sort-amount-desc',
        'charge'               => 'battery-half',
        'chromatic_dispersion' => 'indent',
        'cooling'              => 'thermometer-full',
        'count'                => 'hashtag',
        'current'              => 'bolt fa-flip-horizontal',
        'dbm'                  => 'sun-o',
        'delay'                => 'clock-o',
        'eer'                  => 'snowflake-o',
        'fanspeed'             => 'refresh',
        'frequency'            => 'line-chart',
        'humidity'             => 'tint',
        'load'                 => 'percent',
        'loss'                 => 'percentage',
        'power'                => 'power-off',
        'power_consumed'       => 'plug',
        'power_factor'         => 'calculator',
        'pressure'             => 'thermometer-empty',
        'quality_factor'       => 'arrows',
        'runtime'              => 'hourglass-half',
        'signal'               => 'wifi',
        'snr'                  => 'signal',
        'state'                => 'bullseye',
        'temperature'          => 'thermometer-three-quarters',
        'voltage'              => 'bolt',
        'waterflow'            => 'tint',
        'percent'              => 'percent',
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

    // ---- Define Relationships ----
    public function events()
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }
}
