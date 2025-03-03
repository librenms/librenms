<?php

namespace App\Models;

class PortStatistic extends PortRelatedModel
{
    protected $table = 'ports_statistics';
    protected $primaryKey = 'port_id';
    public $timestamps = false;
}
