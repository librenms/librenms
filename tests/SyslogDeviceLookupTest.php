<?php

/**
 * SyslogDeviceLookupTest.php
 *
 * Tests that a syslog sender is matched to the right device
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
 * @copyright  2026 Jacob Wilkins
 * @author     Jacob Wilkins <jacob@9.nz>
 */

namespace LibreNMS\Tests;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Util\IPv6;

/**
 * A syslog sender identifies itself with whatever string it likes, so the lookup has to
 * match a hostname, a sysName, the device address, or an address on one of its ports.
 */
final class SyslogDeviceLookupTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        DeviceCache::flush();
    }

    public function testSenderIsFoundByAnyIdentifierItMightUse(): void
    {
        $ipv6 = new IPv6('2001:db8::20');

        /** @var Device $device */
        $device = Device::factory()->create([
            'hostname' => 'syslog-sender.example.com',
            'sysName' => 'syslog-sender',
            'ip' => '192.0.2.10',
        ]);

        /** @var Port $port */
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        Ipv4Address::factory()->create(['port_id' => $port->port_id, 'ipv4_address' => '198.51.100.20']);
        Ipv6Address::factory()->create([
            'port_id' => $port->port_id,
            'ipv6_address' => $ipv6->uncompressed(),
            'ipv6_compressed' => $ipv6->compressed(),
        ]);

        $senders = [
            'hostname' => 'syslog-sender.example.com',
            'sysName' => 'syslog-sender',
            'device address' => '192.0.2.10',
            'port ipv4 address' => '198.51.100.20',
            'port ipv6 address' => '2001:db8::20',
        ];

        foreach ($senders as $identifier => $sender) {
            $cache = [];

            $this->assertEquals(
                $device->device_id,
                syslog_device($sender, $cache)?->device_id,
                "A sender identifying itself by $identifier should be matched to the device."
            );
        }
    }

    public function testDeletedDeviceIsNotResolved(): void
    {
        $cache = [];
        $device = Device::factory()->create(['hostname' => 'gone.example.com']);

        $this->assertNotNull(syslog_device('gone.example.com', $cache));

        $device->delete();
        DeviceCache::flush();

        // DeviceCache::get() answers with an empty model rather than null
        $this->assertNull(syslog_device('gone.example.com', $cache));
    }

    public function testUnknownSenderIsNotRemembered(): void
    {
        $cache = [];

        $this->assertNull(syslog_device('192.0.2.99', $cache));

        // a device added while the receiver is running must still be picked up
        $device = Device::factory()->create(['hostname' => '192.0.2.99']);
        $found = syslog_device('192.0.2.99', $cache);

        $this->assertNotNull($found, 'A miss must not be remembered.');
        $this->assertEquals($device->device_id, $found->device_id);
    }
}
