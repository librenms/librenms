<?php

namespace App\Models;

class Sensor extends BaseModel
{
    public $timestamps = false;
    protected $primaryKey = 'sensor_id';

    protected static $icons = array(
        'fanspeed' => 'tachometer',
        'humidity' => 'tint',
        'temperature' => 'thermometer-full',
        'current' => 'bolt',
        'frequency' => 'line-chart',
        'power' => 'power-off',
        'voltage' => 'bolt',
        'charge' => 'battery-half',
        'dbm' => 'sun-o',
        'load' => 'percent',
        'runtime' => 'hourglass-half',
        'state' => 'bullseye',
        'signal' => 'wifi',
        'snr' => 'signal',
        'pressure' => 'thermometer-empty',
        'cooling' => 'thermometer-full',
        'airflow' => 'angle-double-right',
        'delay' => 'clock-o',
        'chromatic_dispersion' => 'indent',
        'ber' => 'sort-amount-desc',
        'quality_factor' => 'arrows',
        'eer' => 'snowflake-o',
        'waterflow' => 'tint',
    );

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
        return collect(self::$icons)->get($this->sensor_class, 'heartbeat');
    }

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }

    public function events()
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }
}
