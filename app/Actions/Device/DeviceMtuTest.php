<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\Ping;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;

class DeviceMtuTest
{
    private readonly ?int $bytes;
    private readonly Ping|Fping $tester;
    private readonly IcmpPollingMethod $icmpMethod;

    public function __construct(
        ?IcmpPollingMethod $icmpMethod = null,
    ) {
    {
        $this->bytes = LibrenmsConfig::get('mtu_options.bytes');

        if (LibrenmsConfig::get('mtu_options.fragmentation', 'pmtu') == 'allow') {
            $this->tester = new Ping();
        } else {
            $this->tester = match (LibrenmsConfig::get('mtu_options.command', 'fping')) {
                'ping' => new Ping(),
                default => new Fping(),
            };
        }

        $this->bytes = LibrenmsConfig::get('mtu_options.bytes');
        $this->icmpMethod = $icmpMethod ?? app(IcmpPollingMethod::class);
    }

    public function execute(Device $device): bool
    {
        if (! $device->polling()->isEnabled(PollingMethodType::Icmp)) {
            return true;
        }

        if ($this->bytes === null) {
            return true;
        }

        return $this->tester->testMtu(
            $device->pollerTarget(),
            $this->bytes,
            $this->icmpMethod->resolveAddressFamily($device),
        );
    }
}
