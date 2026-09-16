<?php

/**
 * PortDescrParserTest.php
 *
 * Test the interface description parser (port_descr_parser) when an ifAlias
 * override is set via the device "ifName:{ifName}" attribute.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Tests;

use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use DeviceCache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Util\ModuleList;

final class PortDescrParserTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testIfAliasOverrideFeedsPortParser(): void
    {
        ->requireSnmpsim();

        // Lock testing time
        ->travelTo(new \DateTime('2022-01-01 00:00:00'));

        // stub out Eventlog::log and Fping->ping, we don't need to store them for these tests
        ->stubClasses();

        // don't store time series data
        LibrenmsConfig::set('rrd.enable', false);
        LibrenmsConfig::set('rrdtool_version', '1.7.2');

        // enable the built-in interface description parser
        LibrenmsConfig::set('port_descr_parser', 'includes/port-descr-parser.inc.php');

        // add the test device pointed at the snmpsim data
         = new Device([
            'hostname' => ->getSnmpsimIp(),
            'snmpver' => 'v2c',
            'transport' => 'udp',
            'community' => 'ios',
            'port' => ->getSnmpsimPort(),
            'disabled' => 1, // disable to block normal pollers
        ]);
        (new ValidateDeviceAndCreate(, true))->execute();
         = ->device_id;

        DeviceCache::flush();
        DeviceCache::setPrimary();

        // discover ports (os detection happens in the core module)
        (new DiscoverDevice(, new ModuleList(['ports' => true])))->handle();

        /** @var Port  */
         = Port::query()
            ->where('device_id', )
            ->where('deleted', 0)
            ->whereNotNull('ifName')
            ->orderBy('ifIndex')
            ->first();

        ->assertNotNull(, 'No port discovered for the simulated device');

        // override ifAlias for this port, the parser should read this value
         = 'cust: Acme Corp [1Gbps] {CIRCUIT-123} (note)';
        Device::find()->setAttrib('ifName:' . ->ifName, );
        DeviceCache::flush();
        DeviceCache::setPrimary();

        // poll ports
        (new PollDevice(, new ModuleList(['ports' => true])))->handle();

        ->refresh();

        ->assertSame('cust', ->port_descr_type);
        ->assertSame('Acme Corp', ->port_descr_descr);
        ->assertSame('CIRCUIT-123', ->port_descr_circuit);
        ->assertSame('1Gbps', ->port_descr_speed);
        ->assertSame('note', ->port_descr_notes);

        DeviceCache::flush();
        ->travelBack();
    }

    private function stubClasses(): void
    {
        ->app->bind(Eventlog::class, function () {
             = \Mockery::mock(Eventlog::class);
            ->shouldReceive('_log');

            return ;
        });

        ->app->bind(Fping::class, function () {
             = \Mockery::mock(Fping::class);
            ->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());

            return ;
        });
    }
}
