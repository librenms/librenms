<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class AccessPoint extends DeviceRelatedModel implements Keyable
{
    use HasFactory;
    protected $primaryKey = 'accesspoint_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'name',
        'radio_number',
        'type',
        'mac_addr',
        'channel',
        'txpow',
        'radioutil',
        'numasoclients',
        'nummonclients',
        'numactbssid',
        'nummonbssid',
        'interference',
    ];

    public function getCompositeKey(): string
    {
        return "{$this->name}_{$this->radio_number}";
    }
}
