<?php

/**
 * AvailabilityTest.php
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

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Availability;
use LibreNMS\OS;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;
use Mockery\MockInterface;

final class AvailabilityTest extends InMemoryDbTestCase
{
    public function testPollingMethodsAreTheMethodsThatAffectAvailability(): void
    {
        $device = Device::factory()->create();
        DevicePollingMethod::factory()->for($device)->create(['method_type' => PollingMethodType::Icmp, 'affects_availability' => true]);
        DevicePollingMethod::factory()->for($device)->create(['method_type' => PollingMethodType::Snmp, 'affects_availability' => true, 'enabled' => false]);
        DevicePollingMethod::factory()->for($device)->create(['method_type' => PollingMethodType::UnixAgent, 'affects_availability' => false]);

        $this->assertSame([PollingMethodType::Icmp, PollingMethodType::Snmp], (new Availability)->pollingMethods($device));
    }
}
