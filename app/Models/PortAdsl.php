<?php

namespace App\Models;

class PortAdsl extends PortRelatedModel
{
    protected $fillable = ['port_id'];
    protected $table = 'ports_adsl';
    protected $primaryKey = 'port_id';
    public $timestamps = false;
}
