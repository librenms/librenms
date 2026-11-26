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

    /**
     * Sensors are named by their card (the index rounded to hundreds) and their description
     */
    protected function entitySensorDescr(int $index, array $entities): string
    {
        $card = (int) floor($index / 100) * 100;

        return ucwords($entities[$card]['entPhysicalName'] ?? '') . ' ' . ucwords($entities[$index]['entPhysicalDescr'] ?? '');
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
