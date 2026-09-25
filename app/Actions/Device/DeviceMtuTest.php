<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;

class DeviceMtuTest
{
    private readonly ?int $bytes;
    private readonly IcmpPollingMethod $icmpMethod;

    public function __construct(
        private readonly Fping $fping,
        ?IcmpPollingMethod $icmpMethod = null,
    ) {
        $this->bytes = LibrenmsConfig::get('mtu_options.bytes');
        $this->icmpMethod = $icmpMethod ?? app(IcmpPollingMethod::class);
    }

    public function execute(Device $device): bool
    {
        if (! (new ConnectivityHelper($device))->icmpIsEnabled()) {
            return true;
        }

        if ($this->bytes === null) {
            return true;
        }

        return $this->fping->testMtu(
            $device->pollerTarget(),
            $this->bytes,
            $this->icmpMethod->resolveAddressFamily($device),
        );
    }
}
