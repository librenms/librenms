<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentStatusLog extends Model
{
    public $timestamps = false;
    protected $table = 'component_statuslog';
    protected $fillable = ['component_id', 'status', 'message'];

    // ---- Accessors/Mutators ----

    public function setStatusAttribute($status)
    {
        $this->attributes['status'] = (int) $status;
    }
}
