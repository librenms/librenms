<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Enum\AddressFamily;
use Symfony\Component\Process\Process;

class TracerouteToDevice
{
    public function execute(Device $device): string
    {
        $command = [LibrenmsConfig::get('traceroute', 'traceroute'), '-q', '1', '-w', '1', '-I', $device->pollerTarget()];

        if ($device->ipFamily() == AddressFamily::IPv6) {
            $command[] = '-6';
        }

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        return $process->getOutput() . $process->getErrorOutput();
    }
}
