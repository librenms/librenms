<?php

namespace App\Models;

class PortStack extends BaseModel
{
    protected $table = 'ports_stack';

    public $timestamps = false;
    protected $primaryKey = 'device_id';

    protected $filliable = ['port_id_high', 'port_id_low', 'ifStackStatus'];

    public function scopeIsActive($query)
    {
        return $query->where('ifStackStatus', 'active');
    }

    public function scopeValidMappings($query)
    {
        return $query->where('port_id_high', '!=', '0')->where('port_id_low', '!=', '0');
    }
}