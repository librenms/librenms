<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS;
use LibreNMS\OS\Traits\EntityMib;
use SnmpQuery;

class EltexMes24xx extends OS
{
    use EntityMib {
        EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = $this->discoverBaseEntityPhysical();

        // add SFPs
        $oidSfp = SnmpQuery::hideMib()->enumStrings()->walk('ELTEX-PHY-MIB::eltexPhyTransceiverInfoTable')->table(1);
        $ifIndexToEntIndexMap = array_flip($this->getIfIndexEntPhysicalMap());

        foreach ($oidSfp as $ifIndex => $data) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1000000 + $ifIndex,
                'entPhysicalSerialNum' => $data['eltexPhyTransceiverInfoSerialNumber'],
                'entPhysicalModelName' => $data['eltexPhyTransceiverInfoPartNumber'],
                'entPhysicalName' => $data['eltexPhyTransceiverInfoConnectorType'],
                'entPhysicalDescr' => $data['eltexPhyTransceiverInfoType'],
                'entPhysicalClass' => 'sfp-cage',
                'entPhysicalContainedIn' => $ifIndexToEntIndexMap[$ifIndex] ?? 0,
                'entPhysicalMfgName' => $data['eltexPhyTransceiverInfoVendorName'],
                'entPhysicalHardwareRev' => $data['eltexPhyTransceiverInfoVendorRevision'],
                'entPhysicalIsFRU' => 'true',
                'ifIndex' => $ifIndex,
            ]));
        }

        return $inventory;
    }
}
