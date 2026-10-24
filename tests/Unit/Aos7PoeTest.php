<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Tests\TestCase;

class Aos7PoeTest extends TestCase
{
    public function testCapturedPowerIsMappedToVirtualChassisPorts(): void
    {
        $values = [];
        foreach (file(base_path('tests/snmpsim/aos7.snmprec'), FILE_IGNORE_NEW_LINES) as $record) {
            if (preg_match('/^1\.3\.6\.1\.4\.1\.6486\.801\.1\.2\.1\.27\.1\.1\.1\.1\.([12])\.(\d+\.\d+)\|2\|(\d+)$/', $record, $matches)) {
                $values[".1.3.6.1.4.1.6486.801.1.2.1.27.1.1.1.1.{$matches[1]}.{$matches[2]}"] = $matches[3];
            }
        }
        $this->assertCount(96, $values);

        $query = \Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('numeric')->once()->andReturnSelf();
        $query->shouldReceive('walk')->once()->with([
            '.1.3.6.1.4.1.6486.801.1.2.1.27.1.1.1.1.1',
            '.1.3.6.1.4.1.6486.801.1.2.1.27.1.1.1.1.2',
        ])->andReturn(new SnmpResponse($values));
        $this->app->instance(SnmpQueryBuilder::class, $query);
        LibrenmsConfig::set('enable_ports_poe', true);

        // Deliberately use unrelated ifIndexes to verify mapping by physical port name.
        $port_stats = [
            71 => ['ifName' => '1/1/3'],
            72 => ['ifName' => '2/1/1'],
            73 => ['ifName' => '1/1/1'],
            74 => ['ifName' => '1/2/1'],
            75 => ['ifName' => 'Loopback0'],
        ];
        $fetched_data_string = '';
        require base_path('includes/polling/ports/os/aos7.inc.php');

        $this->assertSame('10000', $port_stats[71]['alaPethPsePortPowerActual']);
        $this->assertSame('9700', $port_stats[72]['alaPethPsePortPowerActual']);
        $this->assertSame('0', $port_stats[73]['alaPethPsePortPowerActual']);
        $this->assertSame('95000', $port_stats[71]['alaPethPsePortPowerMaximum']);
        $this->assertSame(['ifName' => '1/2/1'], $port_stats[74]);
        $this->assertSame(['ifName' => 'Loopback0'], $port_stats[75]);
    }

    public function testDisabledPoeDoesNotQuerySnmp(): void
    {
        $query = \Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldNotReceive('numeric');
        $this->app->instance(SnmpQueryBuilder::class, $query);
        LibrenmsConfig::set('enable_ports_poe', false);
        $port_stats = [71 => ['ifName' => '1/1/3']];
        require base_path('includes/polling/ports/os/aos7.inc.php');
        $this->assertSame([71 => ['ifName' => '1/1/3']], $port_stats);
    }

    public function testIdlePortWritesZeroConsumptionAndUnknownUnavailableMetrics(): void
    {
        $device = ['os' => 'aos7'];
        $port_id = 71;
        $ifName = '1/1/1';
        $this_port = ['alaPethPsePortPowerMaximum' => '95000', 'alaPethPsePortPowerActual' => '0'];
        $datastore = \Mockery::mock();
        $datastore->shouldReceive('put')->once()->with($device, 'poe', \Mockery::on(fn ($tags) => $tags['ifName'] === $ifName), [
            'PortPwrAllocated' => null,
            'PortPwrAvailable' => '95000',
            'PortConsumption' => '0',
            'PortMaxPwrDrawn' => null,
        ]);
        $this->app->instance('Datastore', $datastore);
        $this->expectOutputString('PoE(AOS7) ');
        require base_path('includes/polling/ports/port-poe.inc.php');
    }
}
