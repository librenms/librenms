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
        $this->requireSnmpsim();

        // Lock testing time
        $this->travelTo(new \DateTime('2022-01-01 00:00:00'));

        // stub out Eventlog::log and Fping->ping, we don't need to store them for these tests
        $this->stubClasses();

        // don't store time series data
        LibrenmsConfig::set('rrd.enable', false);
        LibrenmsConfig::set('rrdtool_version', '1.7.2');

        // enable the built-in interface description parser
        LibrenmsConfig::set('port_descr_parser', 'includes/port-descr-parser.inc.php');

        // add the test device pointed at the snmpsim data
        $new_device = new Device([
            'hostname' => $this->getSnmpsimIp(),
            'snmpver' => 'v2c',
            'transport' => 'udp',
            'community' => 'ios',
            'port' => $this->getSnmpsimPort(),
            'disabled' => 1, // disable to block normal pollers
        ]);
        (new ValidateDeviceAndCreate($new_device, true))->execute();
        $device_id = $new_device->device_id;

        DeviceCache::flush();
        DeviceCache::setPrimary($device_id);

        // discover ports (os detection happens in the core module)
        (new DiscoverDevice($device_id, new ModuleList(['ports' => true])))->handle();

        /** @var Port $port */
        $port = Port::query()
            ->where('device_id', $device_id)
            ->where('deleted', 0)
            ->whereNotNull('ifName')
            ->orderBy('ifIndex')
            ->first();

        $this->assertNotNull($port, 'No port discovered for the simulated device');

        // override ifAlias for this port, the parser should read this value
        $ifAlias = 'cust: Acme Corp [1Gbps] {CIRCUIT-123} (note)';
        Device::find($device_id)->setAttrib('ifName:' . $port->ifName, $ifAlias);
        DeviceCache::flush();
        DeviceCache::setPrimary($device_id);

        // poll ports
        (new PollDevice($device_id, new ModuleList(['ports' => true])))->handle();

        $port->refresh();

        $this->assertSame('cust', $port->port_descr_type);
        $this->assertSame('Acme Corp', $port->port_descr_descr);
        $this->assertSame('CIRCUIT-123', $port->port_descr_circuit);
        $this->assertSame('1Gbps', $port->port_descr_speed);
        $this->assertSame('note', $port->port_descr_notes);

        DeviceCache::flush();
        $this->travelBack();
    }

    private function stubClasses(): void
    {
        $this->app->bind(Eventlog::class, function ($app) {
            $mock = \Mockery::mock(Eventlog::class);
            $mock->shouldReceive('_log');

            return $mock;
        });

        $this->app->bind(Fping::class, function ($app) {
            $mock = \Mockery::mock(Fping::class);
            $mock->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());

            return $mock;
        });
    }
}
