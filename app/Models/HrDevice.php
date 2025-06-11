<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class HrDevice extends DeviceRelatedModel implements Keyable
{
    public $timestamps = false;
    protected $table = 'hrDevice';
    protected $primaryKey = 'hrDevice_id';
    protected $fillable = [
        'hrDeviceIndex',
        'hrDeviceDescr',
        'hrDeviceType',
        'hrDeviceErrors',
        'hrDeviceStatus',
        'hrProcessorLoad',
    ];
    protected $attributes = [
        'hrDeviceType' => 'unknown',
    ];

    public function getCompositeKey()
    {
        return $this->hrDeviceIndex;
    }
}
