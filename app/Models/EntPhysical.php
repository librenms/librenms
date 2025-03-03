<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class EntPhysical extends DeviceRelatedModel implements Keyable
{
    protected $table = 'entPhysical';
    protected $primaryKey = 'entPhysical_id';
    public $timestamps = false;
    protected $fillable = [
        'entPhysicalIndex',
        'entPhysicalDescr',
        'entPhysicalContainedIn',
        'entPhysicalClass',
        'entPhysicalName',
        'entPhysicalSerialNum',
        'entPhysicalModelName',
        'entPhysicalMfgName',
        'entPhysicalVendorType',
        'entPhysicalParentRelPos',
        'entPhysicalHardwareRev',
        'entPhysicalFirmwareRev',
        'entPhysicalSoftwareRev',
        'entPhysicalIsFRU',
        'entPhysicalAlias',
        'entPhysicalAssetID',
        'ifIndex',
    ];

    public function getCompositeKey()
    {
        return $this->entPhysicalIndex;
    }
}
