<?php

namespace App\Models;

class Storage extends BaseModel
{

    protected $table = 'storage';

    protected $primaryKey = 'storage_id';

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }
}
