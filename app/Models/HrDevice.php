<?php

namespace App\Models;

use App\Models\Traits\SanitizesStrings;
use LibreNMS\Interfaces\Models\Keyable;

class HrDevice extends DeviceRelatedModel implements Keyable
{
    use SanitizesStrings;

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

    public function getCompositeKey(): int
    {
        return (int) $this->hrDeviceIndex;
    }
}
