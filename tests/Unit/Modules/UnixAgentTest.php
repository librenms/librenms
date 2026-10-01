<?php

/**
 * UnixAgentTest.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Modules;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Modules\UnixAgent;
use LibreNMS\Tests\TestCase;

final class UnixAgentTest extends TestCase
{
    public function testFetchUsesThePollingMethodPort(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $this->assertNotFalse($server);
        $port = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);

        LibrenmsConfig::set('unix-agent.port', 1); // nothing listens here
        LibrenmsConfig::set('unix-agent.read-timeout', 1);

        $device = new Device(['hostname' => '127.0.0.1']);
        $device->setRelation('pollingMethods', collect([new DevicePollingMethod([
            'method_type' => PollingMethodType::UnixAgent,
            'enabled' => true,
            'settings' => ['port' => $port, 'timeout' => 2],
        ])]));

        $log = Log::spy();

        $fetch = new \ReflectionMethod(UnixAgent::class, 'fetch');
        $this->assertEmpty($fetch->invoke(new UnixAgent, $device)); // the listener never sends data

        // connected to the polling method port and timed out reading, instead of failing to connect to the global port
        $log->shouldHaveReceived('error')->with("Connection to UNIX agent timed out during fetch on port $port")->once();

        fclose($server);
    }
}
