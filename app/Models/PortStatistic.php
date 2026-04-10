<?php

namespace App\Models;

class PortStatistic extends PortRelatedModel
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $table = 'ports_statistics';
    protected $primaryKey = 'port_id';
    public $timestamps = false;
}
