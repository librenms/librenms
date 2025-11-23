<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\DeviceCache;
use Illuminate\Support\Arr;
use LibreNMS\Data\Source\Net\AddrInfoResolver;
use LibreNMS\Data\Source\Net\ConnectionFinder;
use LibreNMS\Data\Source\Net\Service\DnsRequestMessage;
use LibreNMS\Data\Source\Net\Service\IcmpConnector;
use LibreNMS\Data\Source\Net\Service\NtpConnector;
use LibreNMS\Data\Source\Net\Service\NtpRequestMessage;
use LibreNMS\Data\Source\Net\Service\SnmpConnector;
use LibreNMS\Data\Source\Net\Service\SnmpRequestMessage;
use LibreNMS\Data\Source\Net\Service\TcpConnector;
use LibreNMS\Data\Source\Net\UdpHappyEyeballsConnector;
use LibreNMS\Util\Dns;
use React\Dns\Resolver\Resolver;
use function React\Async\await;

class TestHappyEyeballs extends LnmsCommand
{
    protected $signature = 'test:happy-eyeballs {hostname?} {service?} {--port=} {--fibers}';


    public function handle(Dns $dns): int
    {
        $this->configureOutputOptions();

        if ($this->option('fibers')) {
            $this->info('Using Custom Fibers');
            return $this->fibers($dns);
        }

        $hostname = $this->argument('hostname');
        if ($hostname) {
            try {

            $device = DeviceCache::get($hostname);
            if ($device->exists) {
                DeviceCache::setPrimary($device->device_id);
            }
            } catch (\Exception $e) {
                // device not found
            }
        }
        $default_port = match($this->argument('service')) {
            'ntp' => 123,
            'dns' => 53,
            'snmp' => 161,
            default => 80,
        };

        $port = (int) $this->option('port') ?: $default_port;
        $service = match ($this->argument('service')) {
            'ntp' => NtpRequestMessage::class,
            'dns' => DnsRequestMessage::class,
            'snmp' => SnmpRequestMessage::class,
            default => TcpConnector::class,
        };

        $start_time = microtime(true);

        if ($service === TcpConnector::class) {
            return $this->reactTcp($hostname, $port);
        }

        $dns = new Resolver(new AddrInfoResolver);
        $connector = new UdpHappyEyeballsConnector($dns);
        $request = new $service();

        await($connector->connect($hostname, $port, $request)->then(
            function ($result) use ($service) {
                echo "$service connected via: " . $result['address'] . "\n";
                echo "Response length: " . strlen($result['response']) . " bytes\n";
                $result['socket']->close();
            },
            function ($error) use ($service) {
                echo "$service connection failed: " . $error->getMessage() . "\n";
            }
        ));

        $this->info("Elapsed time: " . (microtime(true) - $start_time) . " seconds");

        return 0;
    }

    private function reactTcp(string $hostname, int $port): int
    {
        $reactDns = new Resolver(new AddrInfoResolver);
        $tcpConnector = new \React\Socket\TcpConnector();
        $dnsConnector = new \React\Socket\HappyEyeBallsConnector(null, $tcpConnector, $reactDns);

        $dnsConnector->connect("$hostname:$port")->then(function (\React\Socket\ConnectionInterface $connection) {
            $this->info("Connected to: " . $connection->getRemoteAddress());
            $connection->end();
        });

        return 0;
    }

    public function fibers(Dns $dns): int
    {
        $this->configureOutputOptions();

        $hostname = $this->argument('hostname');
        if ($hostname) {
            try {
                $device = DeviceCache::get($hostname);
                if ($device->exists) {
                    DeviceCache::setPrimary($device->device_id);
                }
            } catch (\Exception $e) {
                //
            }
        }

        $resolved_ips = $dns->resolveIPs($hostname ?? 'localhost');
        $target_ips = Arr::flatten($resolved_ips);

        $service = match ($this->argument('service')) {
            'icmp' => IcmpConnector::class,
            'ntp' => NtpConnector::class,
            'snmp' => SnmpConnector::class,
            default => TcpConnector::class,
        };

        $this->info('Target IPs: ' . implode(', ', $target_ips));
        $start_time = microtime(true);

        // Execute the happy eyeballs function
        $connector = new ConnectionFinder();
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
