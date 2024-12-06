<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class StateIndex extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'state_indexes';
    protected $fillable = ['state_name'];
    protected $primaryKey = 'state_index_id';

    public function sensors(): HasManyThrough
    {
        return $this->hasManyThrough(Sensor::class, SensorToStateIndex::class, 'state_index_id', 'sensor_id', 'state_index_id', 'sensor_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StateTranslation::class, 'state_index_id', 'state_index_id');
    }
}
