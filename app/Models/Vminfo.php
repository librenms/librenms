<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
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

    public function getOperatingSystemAttribute()
    {
        if (Str::contains($this->vmwVmGuestOS, 'tools not installed')) {
            return 'Unknown (VMware Tools not installed)';
        } elseif (Str::contains($this->vmwVmGuestOS, 'tools not running')) {
            return 'Unknown (VMware Tools not running)';
        } elseif (empty($this->vmwVmGuestOS)) {
            return '(Unknown)';
        } else {
            return Rewrite::vmwareGuest($this->vmwVmGuestOS);
        }
    }
}
