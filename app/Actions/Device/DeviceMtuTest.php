<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Polling\ConnectivityHelper;
use Log;
use Symfony\Component\Process\Process;

class DeviceMtuTest
{
    private readonly ?int $bytes;

    public function __construct()
    {
        $this->bytes = LibrenmsConfig::get('mtu_options.bytes');
    }

    public function execute(Device $device): bool
    {
        if (! ConnectivityHelper::pingIsAllowed($device)) {
            return true;
        }

        if ($this->bytes == null) {
            return true;
        }

        $bytes = $this->bytes > 28 ? $this->bytes - 28 : $this->bytes;

        $cmd = array_merge(LibrenmsConfig::fpingCommand($device->ipFamily()), [
            '-q',
            '-b',
            $bytes,
            $device->pollerTarget(),
        ]);

        Log::debug('[MTU] ' . implode(' ', $cmd) . PHP_EOL);

        $fping = new Process($cmd);
        $fping->disableOutput();
        $fping->run();

        return $fping->isSuccessful();
    }
}
