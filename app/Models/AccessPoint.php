<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use LibreNMS\Interfaces\Models\Keyable;

class AccessPoint extends DeviceRelatedModel implements Keyable
{
    use HasFactory;
    use SoftDeletes;

    public $primaryKey = 'accesspoint_id';
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

    public function getCompositeKey()
    {
        return "$this->device_id-$this->mac_addr-$this->radio_number";
    }
}
