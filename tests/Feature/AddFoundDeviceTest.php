<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Device\AddFoundDevice;
use App\Actions\Device\AutoDiscoverDevice;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Add Found Device')]
final class AddFoundDeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    #[TestDox('A device that does not reply to SNMP is unreachable, with the reason')]
    public function testUnreachable(): void
    {
        $this->mockNoSnmpReply();

        $result = app(AddFoundDevice::class)->execute(new Device(['hostname' => 'no-snmp.example.com']));

        $this->assertSame(DeviceAddOutcome::Unreachable, $result->outcome);
        $this->assertStringContainsString('No reply using credential "scan default"', $result->message);
        $this->assertFalse(Device::where('hostname', 'no-snmp.example.com')->exists());
    }

    #[TestDox('Autodiscovery logs why a neighbor was not added')]
    public function testAutoDiscoveryLogsReason(): void
    {
        $this->mockNoSnmpReply();
        LibrenmsConfig::set('nets', ['192.0.2.0/24']);
        LibrenmsConfig::set('discovery_by_ip', true);
        $source = Device::factory()->create();

        $this->assertNull(app(AutoDiscoverDevice::class)->execute('192.0.2.20', $source, 'LLDP'));

        $message = Eventlog::where('device_id', $source->device_id)->where('type', 'discovery')->value('message');
        $this->assertStringStartsWith('LLDP discovery of 192.0.2.20 (192.0.2.20) failed - Could not connect to 192.0.2.20 using SNMP', (string) $message);
    }

    private function mockNoSnmpReply(): void
    {
        $secret = Secret::factory()->create([
            'secret_type' => SecretType::Snmp,
            'description' => 'scan default',
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);
        LibrenmsConfig::set('snmp.default_credentials', [$secret->id]);

        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());
        $this->app->instance(Fping::class, $fping);

        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturn(new RawSnmpResponse('', 'Timeout', 1));
        $this->app->instance(SnmpBackendInterface::class, $backend);
    }
}
