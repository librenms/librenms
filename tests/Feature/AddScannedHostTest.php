<?php

namespace LibreNMS\Tests\Feature;

use App\Jobs\AddScannedHost;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\PollingMethodType;
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

        $this->assertSame(DeviceAddOutcome::NoDns, Bus::dispatchNow(new AddScannedHost('192.0.2.10', dnsOnly: true)));
        $this->assertFalse(Device::where('hostname', '192.0.2.10')->exists());
    }

    #[TestDox('Adds a ping only host by its resolved hostname')]
    public function testAddedPingOnly(): void
    {
        $this->mockDns('scanned.example.com');
        $this->mockFping(FpingResponse::artificialUp());

        $this->assertSame(DeviceAddOutcome::Added, Bus::dispatchNow(new AddScannedHost('192.0.2.11', pollerGroup: 0, pingOnly: true)));

        $device = Device::where('hostname', 'scanned.example.com')->firstOrFail();
        $this->assertSame('ping', $device->os);
        $this->assertTrue($device->polling()->isEnabled(PollingMethodType::Icmp));
    }

    #[TestDox('An unpingable host is reported as unpingable')]
    public function testUnpingable(): void
    {
        $this->mockDns(null);
        $this->mockFping(FpingResponse::artificialDown());

        $this->assertSame(DeviceAddOutcome::Unpingable, Bus::dispatchNow(new AddScannedHost('192.0.2.12', pingOnly: true)));
    }

    #[TestDox('An existing device is reported as known')]
    public function testKnown(): void
    {
        $device = Device::factory()->create();
        $this->mockDns($device->hostname);

        $this->assertSame(DeviceAddOutcome::Known, Bus::dispatchNow(new AddScannedHost('192.0.2.13')));
    }

    #[TestDox('scan:host exits with the outcome of the job')]
    public function testScanHostExitCode(): void
    {
        $this->mockDns(null);

        $this->artisan('scan:host', ['ip' => '192.0.2.14', '--dns-only' => true])
            ->assertExitCode(DeviceAddOutcome::NoDns->value)
            ->execute();
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
