<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class AccessPoint extends DeviceRelatedModel implements Keyable
{
    use HasFactory;

    public $primaryKey = 'accesspoint_id';
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'name',
        'radio_number',
        'type',
        'mac_addr',
        'deleted',
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
        return "$this->mac_addr-$this->radio_number";
    }

    public function setOffline()
    {
        // Todo: implement Laravel's soft-delete
        $this->deleted = 1;
    }
}
