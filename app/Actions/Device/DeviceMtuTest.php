<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Polling\ConnectivityHelper;
use Log;

class DeviceMtuTest
{
    private readonly string $fping_bin;
    private readonly string|false $fping6_bin;
    private readonly int $bytes;

    public function __construct()
    {
        // prep fping parameters
        $this->fping_bin = LibrenmsConfig::get('fping', 'fping');
        $fping6 = LibrenmsConfig::get('fping6', 'fping6');
        $this->fping6_bin = is_executable($fping6) ? $fping6 : false;
        $this->bytes = LibrenmsConfig::get('fping_options.bytes', 64) - 8;
    }

    public function execute(Device $device): bool
    {
        if (! ConnectivityHelper::pingIsAllowed($device)) {
            return true;
        }

        $cmd = match ($device->ipFamily()) {
            AddressFamily::IPv4 => $this->fping6_bin === false ? [$this->fping_bin, '-4'] : [$this->fping_bin],
            AddressFamily::IPv6 => $this->fping6_bin === false ? [$this->fping_bin, '-6'] : [$this->fping6_bin],
        };

        // build the command
        $cmd = array_merge($cmd, [
            '-q',
            '-b',
            $this->bytes,
            $device->pollerTarget(),
        ]);

        Log::debug('[MTU] ' . implode(' ', $cmd) . PHP_EOL);

        $fping = proc_open($cmd, [], $pipes);
        return (proc_close($fping) == 0);
    }
}
