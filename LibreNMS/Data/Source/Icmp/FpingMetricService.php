<?php

namespace LibreNMS\Data\Source\Icmp;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use LibreNMS\Enum\AddressFamily;
use Log;
use Symfony\Component\Process\Process;

class FpingMetricService
{
    private int $count;
    private int $timeout;
    private int $interval;
    private int $tos;

    public function __construct()
    {
        $this->count = max(LibrenmsConfig::get('fping_options.count', 3), 1);
        $this->interval = max(LibrenmsConfig::get('fping_options.interval', 500), 20);
        $this->timeout = max(LibrenmsConfig::get('fping_options.timeout', 500), $this->interval);
        $this->tos = LibrenmsConfig::get('fping_options.tos', 0);
    }

    /**
     * Run fping against a hostname/ip in count mode and collect stats.
     *
     * @param  string  $host  hostname or ip
     * @param  AddressFamily  $address_family  ipv4 or ipv6
     * @return FpingResponse
     */
    public function ping(string $host, AddressFamily $address_family = AddressFamily::IPv4): FpingResponse
    {
        $measure = Measurement::start('ping');

        $cmd = FpingCommandBuilder::make()
            ->forAddressFamily($address_family)
            ->showElapsedTimes()
            ->quiet()
            ->withCount($this->count)
            ->withInterval($this->interval)
            ->withTimeout($this->timeout)
            ->withTos($this->tos)
            ->build($host);

        $process = app()->make(Process::class, ['command' => $cmd]);
        Log::debug('[FPING] ' . $process->getCommandLine() . PHP_EOL);
        $process->run();

        $response = FpingResponse::parseLine($process->getErrorOutput(), $process->getExitCode());
        $measure->manager()->recordFping($measure->end());

        Log::debug("response: $response");

        return $response;
    }
}
