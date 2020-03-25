<?php

namespace App\Models;

class PortAssociationMode extends BaseModel
{
    protected $table = 'port_association_mode';

    protected $primaryKey = 'pom_id';

    public $fillable = [
        'name'
    ];

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'port_association_mode', 'pom_id');
    }
}
