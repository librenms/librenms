<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessPoint extends DeviceRelatedModel
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
}
