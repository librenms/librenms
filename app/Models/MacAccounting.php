<?php

namespace App\Models;

class MacAccounting extends PortRelatedModel
{
    protected $table = 'mac_accounting';
    protected $primaryKey = 'ma_id';
    public $timestamps = false;
}
