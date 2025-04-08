<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class PortStack extends DeviceRelatedModel implements Keyable
{
    use HasFactory;
    protected $table = 'ports_stack';
    public $timestamps = false;
    protected $fillable = [
        'high_ifIndex',
        'high_port_id',
        'low_ifIndex',
        'low_port_id',
        'ifStackStatus',
    ];

    public function getCompositeKey()
    {
        return $this->high_ifIndex . '-' . $this->low_ifIndex;
    }
}
