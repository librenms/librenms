<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\Ping;
use LibreNMS\Polling\ConnectivityHelper;

class DeviceMtuTest
{
    private readonly ?int $bytes;

    public function __construct() {
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

        if (LibrenmsConfig::get('mtu_options.command', 'fping') == 'ping') {
            return (new Ping())->testMtu($device->pollerTarget(), $this->bytes);
        }

        return (new Fping())->testMtu($device->pollerTarget(), $this->bytes, $device->ipFamily());
    }
}
