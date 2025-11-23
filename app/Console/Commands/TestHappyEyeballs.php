<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\DeviceCache;
use Illuminate\Support\Arr;
use LibreNMS\Data\Source\Net\HappyEyeballsConnector;
use LibreNMS\Data\Source\Net\Service\HttpConnector;
use LibreNMS\Data\Source\Net\Service\IcmpConnector;
use LibreNMS\Data\Source\Net\Service\NtpConnector;
use LibreNMS\Data\Source\Net\Service\SnmpConnector;
use LibreNMS\Util\Dns;

class TestHappyEyeballs extends LnmsCommand
{
    protected $signature = 'test:happy-eyeballs {hostname?} {service?} {--port=}';
    const TIMEOUT_SEC = 0;
    const TIMEOUT_USEC = 500000; // 500 milliseconds

    public function handle(Dns $dns): int
    {
        $this->configureOutputOptions();

        $hostname = $this->argument('hostname');
        if ($hostname) {
            $device = DeviceCache::get($hostname);
            if ($device->exists) {
                DeviceCache::setPrimary($device->device_id);
            }
        }

        $resolved_ips = $dns->resolveIPs($hostname ?? 'localhost');
        $target_ips = Arr::flatten($resolved_ips);
        shuffle($target_ips);
        $service = match ($this->argument('service')) {
            'icmp' => IcmpConnector::class,
            'ntp' => NtpConnector::class,
            'snmp' => SnmpConnector::class,
            default => HttpConnector::class,
        };

        $this->info('Target IPs: ' . implode(', ', $target_ips));
        $start_time = microtime(true);

        // Execute the happy eyeballs function
        $connector = new HappyEyeballsConnector();
        $port = (int) $this->option('port');
        $args = $port ? [$port] : [];
        $firstConnectedIp = $connector->connect($target_ips, $service, ...$args);

        $elapsed_time = microtime(true) - $start_time;
        $this->info("Elapsed time: $elapsed_time seconds");

        if ($firstConnectedIp) {
            $this->info("First successful connection was to: $firstConnectedIp");

            return 0;
        }

        $this->error("Failed to connect to any IP address within the timeout.");

        return 1;
    }
}
