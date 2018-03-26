<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
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

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sensors';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'sensors_id';

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

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }
}
