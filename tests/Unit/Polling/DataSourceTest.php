<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Polling\Method\DataSources\ProxmoxDataSource;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\PerDeviceMethodResults;
use LibreNMS\Tests\TestCase;

final class DataSourceTest extends TestCase
{
    public function testOnlyProxmoxHasADataSource(): void
    {
        $device = new Device(['hostname' => 'pve.example.com']);
        $registry = new PollingMethodRegistry;

        foreach (PollingMethodType::cases() as $type) {
            $method = $registry->get($type);
            $source = $method->dataSource($device, $method->config($device));

            if ($type === PollingMethodType::Proxmox) {
                $this->assertInstanceOf(ProxmoxDataSource::class, $source);
            } else {
                $this->assertNull($source, "{$type->value} has no data source");
            }
        }
    }

    public function testDataSourcesOfAvailableMethods(): void
    {
        Http::fake(['*/api2/json/version' => Http::response(['data' => ['version' => '8.2.4']])]);
        $results = new PerDeviceMethodResults($this->device(enabled: true));

        $sources = $results->dataSources(VminfoDiscovery::class);

        $this->assertCount(1, $sources);
        $this->assertInstanceOf(ProxmoxDataSource::class, $sources[0]);
        $this->assertSame($sources, $results->dataSources(VminfoDiscovery::class)); // one source per method and run
        Http::assertSentCount(1); // checked once
    }

    public function testNoDataSourceWhenTheCheckFailsOrTheMethodIsDisabled(): void
    {
        Http::fake(['*' => Http::response(null, 401)]);

        $this->assertSame([], (new PerDeviceMethodResults($this->device(enabled: true)))->dataSources(VminfoDiscovery::class));
        $this->assertSame([], (new PerDeviceMethodResults($this->device(enabled: false)))->dataSources(VminfoDiscovery::class));
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
        $proxmox = new DevicePollingMethod(['method_type' => PollingMethodType::Proxmox, 'enabled' => $enabled]);
        $proxmox->setRelation('secret', new Secret([
            'secret_type' => SecretType::Proxmox,
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
