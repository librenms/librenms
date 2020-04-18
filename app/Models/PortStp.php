<?php

namespace App\Models;

class PortStp extends PortRelatedModel
{
    protected $table = 'ports_stp';
    protected $primaryKey = 'port_stp_id';
    public $timestamps = false;
}
