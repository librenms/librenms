<?php

namespace LibreNMS\OS\Shared;

use App\Models\EntPhysical;
use LibreNMS\OS;
use LibreNMS\Util\StringHelpers;

class Arris extends OS
{
    use OS\Traits\EntityMib {
        OS\Traits\EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverEntityPhysical(): \Illuminate\Support\Collection
    {
        return $this->discoverBaseEntityPhysical()->each(function (EntPhysical $entity): void {
            // clean garbage in Rev fields "...............\n00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00"
            $entity->entPhysicalHardwareRev = StringHelpers::trimHexGarbage($entity->entPhysicalHardwareRev);
            $entity->entPhysicalFirmwareRev = StringHelpers::trimHexGarbage($entity->entPhysicalFirmwareRev);
            $entity->entPhysicalSoftwareRev = StringHelpers::trimHexGarbage($entity->entPhysicalSoftwareRev);
        });
    }
}
