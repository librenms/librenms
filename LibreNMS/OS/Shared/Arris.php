<?php

namespace LibreNMS\OS\Shared;

use App\Models\EntPhysical;
use LibreNMS\OS;

class Arris extends OS
{
    use OS\Traits\EntityMib {
        OS\Traits\EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverEntityPhysical(): \Illuminate\Support\Collection
    {
        return $this->discoverBaseEntityPhysical()->each(function (EntPhysical $entity) {
            // clean garbage in Rev fields "...............\n00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00"
            $regex = '/\.*\n?([0-9a-f]{2} )*(\n[0-9a-f]{2} ?)*$/';
            $entity->entPhysicalHardwareRev = preg_replace($regex,'', $entity->entPhysicalHardwareRev);
            $entity->entPhysicalFirmwareRev = preg_replace($regex,'', $entity->entPhysicalFirmwareRev);
            $entity->entPhysicalSoftwareRev = preg_replace($regex,'', $entity->entPhysicalSoftwareRev);
        });
    }
}
