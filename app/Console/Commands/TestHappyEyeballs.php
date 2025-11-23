<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\DeviceCache;
use Illuminate\Support\Arr;
use LibreNMS\Data\Source\Net\AddrInfoResolver;
use LibreNMS\Data\Source\Net\ConnectionFinder;
use LibreNMS\Data\Source\Net\Service\DnsCodec;
use LibreNMS\Data\Source\Net\Service\IcmpConnector;
use LibreNMS\Data\Source\Net\Service\NtpCodec;
use LibreNMS\Data\Source\Net\Service\NtpConnector;
use LibreNMS\Data\Source\Net\Service\SnmpCodec;
use LibreNMS\Data\Source\Net\Service\SnmpConnector;
use LibreNMS\Data\Source\Net\Service\TcpConnector;
use LibreNMS\Data\Source\Net\Service\UdpCodec;
use LibreNMS\Data\Source\Net\Service\UdpConnector;
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
        $port = $this->resolvePort();
        $codec = $this->resolveUdpCodec();

        $start_time = microtime(true);

        if ($codec === null) {
            return $this->reactTcp($hostname, $port);
        }

        $dns = new Resolver(new AddrInfoResolver);
        $connector = new UdpHappyEyeballsConnector($dns);

        await($connector->connect($hostname, $port, $codec)->then(
            function ($result) {
                echo "connected via: " . $result['address'] . "\n";
                echo "Response length: " . strlen($result['response']) . " bytes\n";
                $result['socket']->close();
            },
            function ($error) {
                echo "connection failed: " . $error->getMessage() . "\n";
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
        $port = $this->resolvePort();
        $resolved_ips = $dns->resolveIPs($hostname ?? 'localhost');
        $target_ips = Arr::flatten($resolved_ips);

        $codec = $this->resolveUdpCodec();
        $socketConnector = $codec === null ? TcpConnector::class : UdpConnector::class;

        $this->info('Target IPs: ' . implode(', ', $target_ips));
        $start_time = microtime(true);

        // Execute the happy eyeballs function
        $finder = new ConnectionFinder();

        $firstConnectedIp = $finder->connect($target_ips, $port, $socketConnector, $codec);

        $elapsed_time = microtime(true) - $start_time;
        $this->info("Elapsed time: $elapsed_time seconds");

        if ($firstConnectedIp) {
            $this->info("First successful connection was to: $firstConnectedIp");

            return 0;
        }

        $this->error("Failed to connect to any IP address within the timeout.");

        return 1;
    }

    private function resolvePort(): int
    {
        $default_port = match($this->argument('service')) {
            'ntp' => 123,
            'dns' => 53,
            'snmp' => 161,
            default => 80,
        };

        return (int) $this->option('port') ?: $default_port;
    }

    private function resolveUdpCodec(): ?UdpCodec
    {
        return match($this->argument('service')) {
            'ntp' => new NtpCodec(),
            'dns' => new DnsCodec(),
            'snmp' => $this->createSnmpCodec(),
            default => null,
        };
    }

    private function createSnmpCodec(): ?SnmpCodec
    {
        $hostname = $this->argument('hostname');
        if ($hostname) {
            try {
                $device = DeviceCache::get($hostname);
                if ($device->exists) {
                    if (str_starts_with($device->transport, 'tcp')) {
                        return null;
                    }

                    return new SnmpCodec(
                        $device->snmpver,
                        $device->community,
                        $device->only(['authname', 'authpass', 'authlevel', 'cryptoalgo', 'cryptopass'])
                    );
                }
            } catch (\Exception $e) {
                //
            }
        }

        return new SnmpCodec();
    }
}
