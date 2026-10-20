<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Jobs\AddScannedHost;
use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\Dns;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Add Scanned Host Job')]
final class AddScannedHostTest extends DBTestCase
{
    use DatabaseTransactions;

    #[TestDox('Skips a host without reverse DNS when DNS is required')]
    public function testNoDns(): void
    {
        $this->mockDns(null);

        $result = Bus::dispatchNow(new AddScannedHost('192.0.2.10', snmpSecretId: null, pingable: true, dnsOnly: true));

        $this->assertSame(DeviceAddOutcome::NoDns, $result->outcome);
        $this->assertFalse(Device::where('hostname', '192.0.2.10')->exists());
    }

    #[TestDox('Adds a host that replied to ping as ping only without pinging it again')]
    public function testAddedPingOnly(): void
    {
        $this->mockDns('scanned.example.com');
        $this->app->instance(Fping::class, Mockery::mock(Fping::class)->shouldNotReceive('ping')->getMock());

        $result = Bus::dispatchNow(new AddScannedHost('192.0.2.11', snmpSecretId: null, pingable: true));

        $this->assertSame(DeviceAddOutcome::Added, $result->outcome);
        $device = Device::where('hostname', 'scanned.example.com')->firstOrFail();
        $this->assertSame('ping', $device->os);
        $this->assertTrue($device->polling()->isEnabled(PollingMethodType::Icmp));
        $this->assertFalse($device->polling()->isEnabled(PollingMethodType::Snmp));
    }

    #[TestDox('A host that was not pinged is pinged before it is added')]
    public function testUnpingable(): void
    {
        $this->mockDns(null);
        $this->mockFping(FpingResponse::artificialDown());

        $result = Bus::dispatchNow(new AddScannedHost('192.0.2.12', snmpSecretId: null, pingable: false));

        $this->assertSame(DeviceAddOutcome::Unpingable, $result->outcome);
    }

    #[TestDox('Adds a host with the secret it replied to, the other default secrets are not tried')]
    public function testAddedWithSecret(): void
    {
        $other = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'other']]);
        $replied = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'replied']]);
        LibrenmsConfig::set('snmp.default_credentials', [$other->id, $replied->id]);
        $this->mockDns('snmp.example.com');

        $communities = [];
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(function ($target, $oids, $config) use (&$communities) {
            $communities[] = $config->community;

            return new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1.3.6.1.4.1.8072.3.2.10', '', 0);
        });
        $backend->shouldIgnoreMissing(new RawSnmpResponse('', '', 0));
        $this->app->instance(SnmpBackendInterface::class, $backend);

        $result = Bus::dispatchNow(new AddScannedHost('192.0.2.14', snmpSecretId: $replied->id, pingable: true));

        $this->assertSame(DeviceAddOutcome::Added, $result->outcome, $result->message);
        $device = Device::where('hostname', 'snmp.example.com')->firstOrFail();
        $this->assertSame($replied->id, $device->pollingMethod(PollingMethodType::Snmp)?->secret_id);
        $this->assertSame(['replied'], array_values(array_unique($communities)));
    }

    #[TestDox('A host that replied to SNMP but not ping is added without ICMP and not pinged again')]
    public function testAddedWithoutIcmp(): void
    {
        $secret = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'replied']]);
        LibrenmsConfig::set('snmp.default_credentials', [$secret->id]);
        $this->mockDns('noping.example.com');
        $this->app->instance(Fping::class, Mockery::mock(Fping::class)->shouldNotReceive('ping')->getMock());

        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturn(new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1.3.6.1.4.1.8072.3.2.10', '', 0));
        $backend->shouldIgnoreMissing(new RawSnmpResponse('', '', 0));
        $this->app->instance(SnmpBackendInterface::class, $backend);

        $result = Bus::dispatchNow(new AddScannedHost('192.0.2.15', snmpSecretId: $secret->id, pingable: false));

        $this->assertSame(DeviceAddOutcome::Added, $result->outcome, $result->message);
        $device = Device::where('hostname', 'noping.example.com')->firstOrFail();
        $this->assertNull($device->pollingMethod(PollingMethodType::Icmp));
        $this->assertTrue($device->polling()->isEnabled(PollingMethodType::Snmp));
    }

    #[TestDox('An existing device is reported as known')]
    public function testKnown(): void
    {
        $device = Device::factory()->create();
        $this->mockDns($device->hostname);

        $result = Bus::dispatchNow(new AddScannedHost('192.0.2.13', snmpSecretId: null, pingable: true));

        $this->assertSame(DeviceAddOutcome::Known, $result->outcome);
    }

    private function mockDns(?string $hostname): void
    {
        $dns = Mockery::mock(Dns::class);
        $dns->shouldReceive('resolveHostname')->andReturn($hostname);
        $this->app->instance(Dns::class, $dns);
    }

    private function mockFping(FpingResponse $response): void
    {
        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andReturn($response);
        $this->app->instance(Fping::class, $fping);
    }
}
