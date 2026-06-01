<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PortStatistic extends PortRelatedModel
{
    use HasFactory;
    protected $table = 'ports_statistics';
    protected $primaryKey = 'port_id';
    public $timestamps = false;
}
