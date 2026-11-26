<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class PortRrdPollingTest extends InMemoryDbTestCase
{
    /** @return array<string, array{int, int, array<string, string>, bool, int}> */
    public static function missingRrdCases(): array
    {
        return [
            'first file at a higher speed' => [0, 400_000, [], true, 50_000_000_000],
            'recreated file at the unchanged override speed' => [400_000_000_000, 100_000, [
                'ifSpeed:Ethernet1' => '400000000000',
                'override_rrdtool_tune' => 'false',
                'ifName_tune:Ethernet1' => 'true',
            ], false, 50_000_000_000],
            'port override disables tuning' => [0, 400_000, [
                'override_rrdtool_tune' => 'true',
                'ifName_tune:Ethernet1' => 'false',
            ], false, 12_500_000_000],
        ];
    }

    #[DataProvider('missingRrdCases')]
    public function testCreatesMissingRrdWithEffectiveLimits(int $storedSpeed, int $polledMbps, array $overrides, bool $attemptsTune, int $maximum): void
    {
        $port = $this->createPort($storedSpeed);
        $device = $port->device;
        foreach ($overrides as $name => $value) {
            $device->setAttrib($name, $value);
        }
        LibrenmsConfig::set('rrdtool_tune', true);
        LibrenmsConfig::set('rrd.heartbeat', 600);

        $rrd = $this->portRrdPath($port);
        $backend = $this->mock(RrdBackendInterface::class);
        if ($attemptsTune) {
            $backend->shouldReceive('tune')->with($rrd, Mockery::any())->once()->ordered()
                ->andThrow(new RrdNotFoundException('No such file'));
        } else {
            $backend->shouldReceive('tune')->never();
        }
        $backend->shouldReceive('update')->with($rrd, Mockery::any(), null)->once()->ordered()
            ->andThrow(new RrdNotFoundException('No such file'));
        $backend->shouldReceive('create')->with($rrd, Mockery::on(fn (RrdDefinition $definition) => array_slice($definition->getArguments(), 0, 2) === [
            "DS:INOCTETS:DERIVE:600:0:$maximum",
            "DS:OUTOCTETS:DERIVE:600:0:$maximum",
        ]))->once()->ordered();
        $backend->shouldReceive('update')->with($rrd, Mockery::any(), null)->once()->ordered();

        $this->pollPorts($device, $polledMbps);

        $this->assertSame(400_000_000_000, (int) $port->fresh()->ifSpeed);
    }

    public function testRaisesExistingRrdLimitsBeforeWritingSample(): void
    {
        $port = $this->createPort(100_000_000_000);
        LibrenmsConfig::set('rrdtool_tune', true);
        $rrd = $this->portRrdPath($port);
        $backend = $this->mock(RrdBackendInterface::class);
        $backend->shouldReceive('create')->never();
        $backend->shouldReceive('tune')->with($rrd, Mockery::on(fn (array $limits) => $limits['INOCTETS']['max'] === 50_000_000_000))->once()->ordered();
        $backend->shouldReceive('update')->with($rrd, Mockery::on(fn (array $values) => $values[0] === '7500000000000'), null)->once()->ordered();

        $this->pollPorts($port->device, 400_000);

        $this->assertSame(400_000_000_000, (int) $port->fresh()->ifSpeed);
    }

    private function portRrdPath(Port $port): Mockery\Matcher\Closure
    {
        $path = $port->device->hostname . "/port-id{$port->port_id}.rrd";

        return Mockery::on(fn (RrdPath $rrd) => $rrd->relativePath() === $path);
    }

    private function createPort(int $speed): Port
    {
        $device = Device::factory()->create(['os' => 'generic']);

        return Port::factory()->for($device)->create([
            'ifIndex' => 1,
            'ifName' => 'Ethernet1',
            'ifDescr' => 'Ethernet1',
            'ifSpeed' => $speed,
        ]);
    }

    private function pollPorts(Device $model, int $speedMbps): void
    {
        $snmp = $this->mock(SnmpBackendInterface::class);
        $snmp->shouldReceive('walk')->andReturnUsing(fn (string $target, string $oid) => new SnmpResponse(match ($oid) {
            'ifXEntry' => ['ifName.1' => 'Ethernet1', 'ifHighSpeed.1' => (string) $speedMbps, 'ifHCInOctets.1' => '7500000000000'],
            'ifDescr' => ['ifDescr.1' => 'Ethernet1'],
            'ifOperStatus' => ['ifOperStatus.1' => 'up'],
            'ifType' => ['ifType.1' => 'ethernetCsmacd'],
            default => [],
        }));
        DeviceCache::setPrimary($model->device_id);
        $device = $model->toArray();

        require base_path('includes/polling/ports.inc.php');
    }
}
