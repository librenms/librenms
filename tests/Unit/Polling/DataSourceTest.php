<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\Polling\Method\DataSources\ProxmoxApiDataSource;
use LibreNMS\Polling\Method\DataSources\UnixAgentDataSource;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Polling\PerDeviceMethodResults;
use LibreNMS\Tests\TestCase;

final class DataSourceTest extends TestCase
{
    public function testMethodsWithADataSource(): void
    {
        $device = new Device(['hostname' => 'pve.example.com']);
        $registry = new PollingMethodRegistry;
        $expected = [
            PollingMethodType::ProxmoxApi->value => ProxmoxApiDataSource::class,
            PollingMethodType::UnixAgent->value => UnixAgentDataSource::class,
        ];

        foreach (PollingMethodType::cases() as $type) {
            $method = $registry->get($type);
            $source = $method->dataSource($method->config($device), ProbeResult::success());

            $this->assertSame($expected[$type->value] ?? null, $source === null ? null : $source::class, $type->value);
        }
    }

    public function testMethodsProvidingAnInterface(): void
    {
        $registry = new PollingMethodRegistry;

        $this->assertSame([PollingMethodType::ProxmoxApi], $registry->providing(VminfoDiscovery::class));
        $this->assertSame([PollingMethodType::ProxmoxApi], $registry->providing(VminfoPolling::class));
        $this->assertSame([PollingMethodType::UnixAgent], $registry->providing(MempoolsDiscovery::class));
    }

    public function testDataSourcesOfAvailableMethods(): void
    {
        Http::fake([
            '*/api2/json/version' => Http::response(['data' => ['version' => '8.2.4']]),
            '*/certificates/info' => Http::response(['data' => [['filename' => 'pve-root-ca.pem', 'fingerprint' => 'AB:CD']]]),
        ]);
        $results = new PerDeviceMethodResults($this->device(enabled: true));

        $sources = $results->dataSources(VminfoDiscovery::class);

        $this->assertCount(1, $sources);
        $this->assertInstanceOf(ProxmoxApiDataSource::class, $sources[0]);
        $this->assertSame($sources, $results->dataSources(VminfoDiscovery::class)); // one source per method and run
        Http::assertSentCount(2); // checked once: the version and the cluster
    }

    public function testNoDataSourceWhenTheCheckFailsOrTheMethodIsDisabled(): void
    {
        Http::fake(['*' => Http::response(null, 401)]);

        $this->assertSame([], new PerDeviceMethodResults($this->device(enabled: true))->dataSources(VminfoDiscovery::class));
        $this->assertSame([], new PerDeviceMethodResults($this->device(enabled: false))->dataSources(VminfoDiscovery::class));
    }

    public function testFailedCheckIsLoggedOnce(): void
    {
        Http::fake(['*' => Http::response(null, 401)]);
        $log = Log::spy();
        $results = new PerDeviceMethodResults($this->device(enabled: true));

        $results->dataSources(VminfoDiscovery::class);
        $results->dataSources(VminfoPolling::class);

        $log->shouldHaveReceived('info')->once()->withArgs(fn (string $message): bool => str_contains($message, 'proxmox') && str_contains($message, '401'));
    }

    public function testMethodsWithoutADataSourceAreNotChecked(): void
    {
        Http::fake();
        $device = new Device(['hostname' => 'pve.example.com']);
        $device->setRelation('pollingMethods', new Collection([
            new DevicePollingMethod(['method_type' => PollingMethodType::UnixAgent, 'enabled' => true]),
        ]));
        $results = new PerDeviceMethodResults($device);

        $this->assertSame([], $results->dataSources(VminfoDiscovery::class));
        $this->assertNull($device->pollingMethod(PollingMethodType::UnixAgent)?->last_check_successful); // not checked
    }

    private function device(bool $enabled): Device
    {
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]); // secret data is encrypted
        $proxmox = new DevicePollingMethod(['method_type' => PollingMethodType::ProxmoxApi, 'enabled' => $enabled]);
        $proxmox->setRelation('secret', new Secret([
            'secret_type' => SecretType::ProxmoxApi,
            'data' => ['token_id' => 'librenms@pve!monitoring', 'token_secret' => 'abc123'],
        ]));

        $device = new Device(['hostname' => 'pve.example.com']);
        $device->setRelation('pollingMethods', new Collection([
            new DevicePollingMethod(['method_type' => PollingMethodType::Icmp, 'enabled' => true]),
            $proxmox,
        ]));

        return $device;
    }
}
