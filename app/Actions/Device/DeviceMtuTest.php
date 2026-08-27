<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Polling\ConnectivityHelper;

class DeviceMtuTest
{
    private readonly ?int $bytes;

    public function __construct(
        private readonly Fping $fping,
    ) {
        $this->bytes = LibrenmsConfig::get('mtu_options.bytes');
    }

    public function execute(Device $device): bool
    {
        if (! (new ConnectivityHelper($device))->icmpIsEnabled()) {
            return true;
        }

        if ($this->bytes === null) {
            return true;
        }

        return $this->fping->testMtu($device->pollerTarget(), $this->bytes, $device->ipFamily());
    }
}
