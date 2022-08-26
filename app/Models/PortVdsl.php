<?php

namespace App\Models;

class PortVdsl extends PortRelatedModel
{
    protected $table = 'ports_vdsl';
    protected $primaryKey = 'port_id';
    public $timestamps = false;
}
