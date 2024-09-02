<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use LibreNMS\OS;
use LibreNMS\Util\StringHelpers;

class SmartaxMdu extends OS
{
    use OS\Traits\EntityMib {
        OS\Traits\EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    protected ?string $entityVendorTypeMib = 'HUAWEI-MIB';

    public function discoverEntityPhysical(): \Illuminate\Support\Collection
    {
        return $this->discoverBaseEntityPhysical()->each(function (EntPhysical $entity) {
            // clean garbage in Rev fields "...............\n00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00"
            $entity->entPhysicalDescr = StringHelpers::trimHexGarbage($entity->entPhysicalDescr);
            $entity->entPhysicalName = StringHelpers::trimHexGarbage($entity->entPhysicalName);
            $entity->entPhysicalHardwareRev = StringHelpers::trimHexGarbage($entity->entPhysicalHardwareRev);
            $entity->entPhysicalFirmwareRev = StringHelpers::trimHexGarbage($entity->entPhysicalFirmwareRev);
            $entity->entPhysicalSoftwareRev = StringHelpers::trimHexGarbage($entity->entPhysicalSoftwareRev);
            $entity->entPhysicalAlias = StringHelpers::trimHexGarbage($entity->entPhysicalAlias);
            $entity->entPhysicalSerialNum = StringHelpers::trimHexGarbage($entity->entPhysicalSerialNum);
            $entity->entPhysicalMfgName = StringHelpers::trimHexGarbage($entity->entPhysicalMfgName);
        });
    }
}
