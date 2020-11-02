<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Enum\PowerState;
use LibreNMS\Util\Rewrite;

class Vminfo extends DeviceRelatedModel
{
    use HasFactory;

    protected $table = 'vminfo';
    public $timestamps = false;

    public function vmDevice()
    {
        return $this->hasOne('App\Models\Device', 'hostname', 'vmwVmDisplayName');
    }

    public function getStateLabelAttribute()
    {
        return PowerState::stateLabel($this->vmwVmState);
    }

    public function getMemoryFormattedAttribute()
    {
        return Rewrite::formatStorage($this->vmwVmMemSize * 1024 * 1024);
    }
}
