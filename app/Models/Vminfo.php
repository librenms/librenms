<?php

namespace App\Models;

use Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;

class Vminfo extends DeviceRelatedModel
{
    use HasFactory;

    protected $table = 'vminfo';
    public $timestamps = false;

    public function getStateLabelAttribute(): array
    {
        return Html::powerStateLabel($this->vmwVmState);
    }

    public function getMemoryFormattedAttribute(): string
    {
        return Number::formatBi($this->vmwVmMemSize * 1024 * 1024);
    }

    public function getOperatingSystemAttribute(): string
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

    public function scopeGuessFromDevice(Builder $query, Device $device): Builder
    {
        $where = [$device->hostname];

        if (Config::get('mydomain')) {
            $where[] = $device->hostname . '.' . Config::get('mydomain');
        }

        return $query->whereIn('vmwVmDisplayName', $where);
    }

    public function parentDevice(): HasOne
    {
        return $this->hasOne('App\Models\Device', 'hostname', 'vmwVmDisplayName');
    }
}
