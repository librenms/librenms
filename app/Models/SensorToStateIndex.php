<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SensorToStateIndex extends Model
{
    protected $table = 'sensors_to_state_indexes';
    protected $primaryKey = 'sensors_to_state_translations_id';
    public $timestamps = false;
    protected $fillable = ['sensor_id', 'state_index_id'];

    public function sensor(): HasOne
    {
        return $this->hasOne(Sensor::class, 'sensor_id', 'sensor_id');
    }

    public function stateIndex(): HasOne
    {
        return $this->hasOne(StateIndex::class, 'state_index_id', 'state_index_id');
    }
}
