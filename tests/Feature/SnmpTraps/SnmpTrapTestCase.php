<?php
/**
 * SnmpTrapTestCase.php
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
use App\Models\Location;
use App\View\SimpleTemplate;
use Illuminate\Support\Arr;
use LibreNMS\Snmptrap\Dispatcher;
use LibreNMS\Tests\TestCase;
use Mockery;

abstract class SnmpTrapTestCase extends TestCase
{
    protected function assertTrapLogsMessage(string $rawTrap, string|array $log, string $failureMessage = '', array $args = [], ?Device $device = null): void
    {
        if ($device === null) {
            $device = Device::factory()->make(['display' => '{{ hostname }}']);
            $device->setRelation('location', Location::factory()->make());
        }

        $template_variables = [
            'hostname' => $device->hostname,
            'ip' => $device->ip,
            'location' => $device->location,
            'sysDescr' => $device->sysDescr,
        ];
        $rawTrap = SimpleTemplate::parse($rawTrap, $template_variables);
        $trap = Mockery::mock('LibreNMS\Snmptrap\Trap[log,getDevice]', [$rawTrap]);
        $trap->shouldReceive('getDevice')->andReturn($device); // mock getDevice to avoid saving to database
        foreach (Arr::wrap($log) as $index => $message) {
            $call_args = is_array($args[$index] ?? null) ? $args[$index] : $args;

            $trap->shouldReceive('log')->once()->with(SimpleTemplate::parse($message, $template_variables), ...$call_args);
        }

        $log_spy = \Log::spy();

        /** @var \LibreNMS\Snmptrap\Trap $trap */
        $this->assertTrue(Dispatcher::handle($trap), $failureMessage);

        // if the test set any log expectations, log_spy will be null
        if ($log_spy != null) {
            $log_spy->shouldNotHaveReceived('error');
            $log_spy->shouldNotHaveReceived('warning');
        }
    }
}
