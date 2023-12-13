<?php

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;

class PortSecurity extends DeviceRelatedModel implements Keyable
{
    use HasFactory;

    protected $table = 'port_security';
    protected $primaryKey = 'port_id';
    public $timestamps = false;
    protected $fillable = [
        'port_id',
        'device_id',
        'cpsIfMaxSecureMacAddr',
        'cpsIfStickyEnable',
    ];

    public function getCompositeKey()
    {
        return $this->port_id;
    }
}
