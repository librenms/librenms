<?php

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\OS;
use SnmpQuery;

class VoltronicAts extends OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // Device often returns placeholder serial of all zeros
        if (preg_match('/^0+$/', (string) $device->serial)) {
            $device->serial = null;
        }

        // Model OID is frequently empty; derive from rated current (e.g. 160 → ATS-16A)
        if (empty($device->hardware)) {
            $rated = SnmpQuery::get('VOLTRONIC-ATS-MIB::atsRatCurrent.0')->value();
            if (is_numeric($rated) && (int) $rated > 0) {
                $device->hardware = 'ATS-' . (int) round(((int) $rated) / 10) . 'A';
            }
        }
    }
}
