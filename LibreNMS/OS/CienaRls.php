<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS;

class CienaRls extends OS
{
    public function discoverEntityPhysical(): Collection
    {
        return \SnmpQuery::hideMib()->enumStrings()->walk('CIENA-6500R-INVENTORY-MIB::rlsCircuitPackTable')->mapTable(function ($entry, $index) {
            return new EntPhysical([
                'entPhysicalIndex' => $index, //need to derive index from the oid
                'entPhysicalDescr' => $entry['rlsCircuitPackCtype'],
                'entPhysicalName' => $entry['rlsCircuitPackCtype'],
                'entPhysicalModelName' => $entry['rlsCircuitPackPec'],
                'entPhysicalSerialNum' => $entry['rlsCircuitPackSerialNumber'] ?? null,
                'entPhysicalParentRelPos' => $index,
                'entPhysicalMfgName' => 'Ciena',
                'entPhysicalAlias' => $entry['rlsCircuitPackCommonLanguageEquipmentIndentifier'] ?? null,
                'entPhysicalHardwareRev' => $entry['rlsCircuitPackHardwareRelease'] ?? null,
                'entPhysicalIsFRU' => 'true',
            ]);
        });
    }
}
