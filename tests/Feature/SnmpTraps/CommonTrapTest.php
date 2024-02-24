<?php
/**
 * CommonTrapTest.php
 *
 * -Description-
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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Ipv4Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\Severity;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\Traits\RequiresDatabase;
use Log;

class CommonTrapTest extends SnmpTrapTestCase
{
    use RequiresDatabase;
    use DatabaseTransactions;

    public function testGarbage(): void
    {
        $trapText = "Garbage\n";

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap), 'Found handler for trap with no snmpTrapOID');
    }

    public function testFindByIp(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(); /** @var Port $port */
        $device->ports()->save($port);
        // test ipv4 lookup of device
        $ipv4 = Ipv4Address::factory()->make(); /** @var Ipv4Address $ipv4 */
        $port->ipv4()->save($ipv4);

        $trapText = "something
UDP: [$ipv4->ipv4_address]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91\n";

        Log::partialMock()->shouldReceive('info')->once()->with('Unhandled trap snmptrap', ['device' => $device->hostname, 'oid' => null]);

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap), 'Found handler for trap with no snmpTrapOID');

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);

        // check that eventlog was logged
        $eventlog = Eventlog::latest('event_id')->first();
        $this->assertEquals($device->device_id, $eventlog->device_id, 'Trap eventlog device incorrect');
        $this->assertEquals('', $eventlog->message, 'Trap eventlog message incorrect');
        $this->assertEquals('trap', $eventlog->type, 'Trap eventlog type incorrect');
        $this->assertEquals(Severity::Info, $eventlog->severity, 'Trap eventlog severity incorrect');
    }

    public function testGenericTrap(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::someOid\n";

        Log::partialMock()->shouldReceive('info')->once()->with('Unhandled trap snmptrap', ['device' => $device->hostname, 'oid' => 'SNMPv2-MIB::someOid']);

        $trap = new Trap($trapText);
        $this->assertFalse(Dispatcher::handle($trap));

        // check that eventlog was logged
        $eventlog = Eventlog::latest('event_id')->first();
        $this->assertEquals($device->device_id, $eventlog->device_id, 'Trap eventlog device incorrect');
        $this->assertEquals('SNMPv2-MIB::someOid', $eventlog->message, 'Trap eventlog message incorrect');
        $this->assertEquals('trap', $eventlog->type, 'Trap eventlog type incorrect');
        $this->assertEquals(Severity::Info, $eventlog->severity, 'Trap eventlog severity incorrect');
    }

    public function testAuthorization(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $trapText = "$device->hostname
UDP: [$device->ip]:64610->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 198:2:10:48.91
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::authenticationFailure\n";

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);

        // check that eventlog was logged
        $eventlog = Eventlog::latest('event_id')->first();
        $this->assertEquals($device->device_id, $eventlog->device_id, 'Trap eventlog device incorrect');
        $this->assertEquals('SNMP Trap: Authentication Failure: ' . $device->displayName(), $eventlog->message, 'Trap eventlog message incorrect');
        $this->assertEquals('auth', $eventlog->type, 'Trap eventlog type incorrect');
        $this->assertEquals(Severity::Notice, $eventlog->severity, 'Trap eventlog severity incorrect');
    }

    public function testBridgeNewRoot(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $trapText = "$device->hostname
UDP: [$device->ip]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 3:4:17:32.35
SNMPv2-MIB::snmpTrapOID.0 BRIDGE-MIB::newRoot";

        $trap = new Trap($trapText);
        $this->assertTrue(Dispatcher::handle($trap));

        // check that the device was found
        $this->assertEquals($device->hostname, $trap->getDevice()->hostname);

        // check that eventlog was logged
        $eventlog = Eventlog::latest('event_id')->first();
        $this->assertEquals($device->device_id, $eventlog->device_id, 'Trap eventlog device incorrect');
        $this->assertEquals('SNMP Trap: Device ' . $device->displayName() . ' was elected as new root on one of its Spanning Tree Instances', $eventlog->message, 'Trap eventlog message incorrect');
        $this->assertEquals('stp', $eventlog->type, 'Trap eventlog type incorrect');
        $this->assertEquals(Severity::Notice, $eventlog->severity, 'Trap eventlog severity incorrect');
    }

    public function testBridgeTopologyChanged(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 3:4:17:32.35
SNMPv2-MIB::snmpTrapOID.0 BRIDGE-MIB::topologyChange
TRAP,
            'SNMP Trap: Topology of Spanning Tree Instance on device {{ hostname }} was changed', // assertTrapLogsMessage sets display to hostname
            'Failed to handle BRIDGE-MIB::topologyChange',
            [Severity::Notice, 'stp'],
        );
    }

    public function testColdStart(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:1:12.7
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::coldStart
TRAP,
            'SNMP Trap: Device {{ hostname }} cold booted',
            'Failed to handle SNMPv2-MIB::coldStart',
            [Severity::Warning, 'reboot'],
        );
    }

    public function testWarmStart(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:2:12.7
SNMPv2-MIB::snmpTrapOID.0 SNMPv2-MIB::warmStart
TRAP,
            'SNMP Trap: Device {{ hostname }} warm booted',
            'Failed to handle SNMPv2-MIB::warmStart',
            [Severity::Warning, 'reboot'],
        );
    }

    public function testEntityDatabaseChanged(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 3:4:17:32.35
SNMPv2-MIB::snmpTrapOID.0 ENTITY-MIB::entConfigChange
TRAP,
            'SNMP Trap: Configuration of Entity Database on device {{ hostname }} was changed',
            'Failed to handle ENTITY-MIB::entConfigChange',
            [Severity::Notice, 'system'],
        );
    }
}
