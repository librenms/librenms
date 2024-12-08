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
 *
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

    public function testFindByHostname(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $found = Device::findByHostname($device->hostname);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }

    public function testFindByIpFail(): void
    {
        $found = Device::findByIp('this is not an ip');
        $this->assertNull($found);
    }

    public function testFindByIpv4Fail(): void
    {
        $found = Device::findByIp('182.43.219.43');
        $this->assertNull($found);
    }

    public function testFindByIpv6Fail(): void
    {
        $found = Device::findByIp('341a:234d:3429:9845:909f:fd32:1930:32dc');
        $this->assertNull($found);
    }

    public function testFindIpButNoPort(): void
    {
        $ipv4 = Ipv4Address::factory()->create(); /** @var Ipv4Address $ipv4 */
        Port::destroy($ipv4->port_id);

        $found = Device::findByIp($ipv4->ipv4_address);
        $this->assertNull($found);
    }

    public function testFindByIp(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $found = Device::findByIp($device->ip);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }

    public function testFindByIpHostname(): void
    {
        $ip = '192.168.234.32';
        $device = Device::factory()->create(['hostname' => $ip]); /** @var Device $device */
        $found = Device::findByIp($ip);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }

    public function testFindByIpThroughPort(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        $port = Port::factory()->make(); /** @var Port $port */
        $device->ports()->save($port);
        // test ipv4 lookup of device
        $ipv4 = Ipv4Address::factory()->make(); /** @var Ipv4Address $ipv4 */
        $port->ipv4()->save($ipv4);

        $found = Device::findByIp($ipv4->ipv4_address);
        $this->assertNotNull($found);
        $this->assertEquals($device->device_id, $found->device_id, 'Did not find the correct device');
    }
}
