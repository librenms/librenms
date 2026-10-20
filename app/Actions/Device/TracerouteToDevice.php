<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Util\Binaries;
use Symfony\Component\Process\Process;

class TracerouteToDevice
{
    public function __construct(
        private readonly Binaries $binaries,
    ) {
    }

    public function execute(Device $device): string
    {
        $command = [$this->binaries->path('traceroute'), '-q', '1', '-w', '1', '-I', $device->pollerTarget()];

        if ($device->ipFamily() == AddressFamily::IPv6) {
            $command[] = '-6';
        }

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        return $process->getOutput() . $process->getErrorOutput();
    }
}
