<?php
/**
 * DeviceTest.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Port;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

class DeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testFindByHostname()
    {
        $device = Device::factory()->create();

        $found = Device::findByHostname($device->hostname);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }

    public function testFindByIpFail()
    {
        $found = Device::findByIp('this is not an ip');
        $this->assertNull($found);
    }

    public function testFindByIpv4Fail()
    {
        $found = Device::findByIp('182.43.219.43');
        $this->assertNull($found);
    }

    public function testFindByIpv6Fail()
    {
        $found = Device::findByIp('341a:234d:3429:9845:909f:fd32:1930:32dc');
        $this->assertNull($found);
    }

    public function testFindIpButNoPort()
    {
        $ipv4 = Ipv4Address::factory()->create();
        Port::destroy($ipv4->port_id);

        $found = Device::findByIp($ipv4->ipv4_address);
        $this->assertNull($found);
    }

    public function testFindByIp()
    {
        $device = Device::factory()->create();

        $found = Device::findByIp($device->ip);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }

    public function testFindByIpHostname()
    {
        $ip = '192.168.234.32';
        $device = Device::factory()->create(['hostname' => $ip]);

        $found = Device::findByIp($ip);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }

    public function testFindByIpThroughPort()
    {
        $device = Device::factory()->create();
        $port = Port::factory()->make();
        $device->ports()->save($port);
        $ipv4 = Ipv4Address::factory()->make(); // test ipv4 lookup of device
        $port->ipv4()->save($ipv4);

        $found = Device::findByIp($ipv4->ipv4_address);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }
}
