<?php

/**
 * DeviceVrfContextsTest.php
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

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\Vrf;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Tests\TestCase;

final class DeviceVrfContextsTest extends TestCase
{
    public function testDefaultContextOnlyWithoutVrfs(): void
    {
        $this->assertSame([''], $this->device([])->getVrfContexts());
    }

    public function testVrfContexts(): void
    {
        $device = $this->device([
            [1, 'red', 'ctx-red'],
            [2, 'blue', null],
            [3, 'Default', 'default'],
            [4, 'green', 'ctx-red'],
            [5, 'yellow', 'ctx-yellow'],
            [6, 'purple', 'ctx-purple', false],
            [7, 'orange', 'ctx-orange', true, false],
        ]);

        $this->assertSame(['', 'ctx-red', 'ctx-yellow'], $device->getVrfContexts(), 'The default VRF context, duplicates, contexts that did not answer and disabled contexts are skipped');
        $this->assertSame(['ctx-purple', 'ctx-orange'], $device->getSkippedVrfContexts(), 'Contexts that did not answer and disabled contexts are kept separately');
    }

    public function testVrfIdForContext(): void
    {
        $device = $this->device([
            [1, 'red', 'ctx-red'],
            [2, 'blue', null],
            [3, 'ctx-red', null],
        ]);

        $this->assertNull($device->vrfIdForContext(''));
        $this->assertNull($device->vrfIdForContext(null));
        $this->assertSame(1, $device->vrfIdForContext('ctx-red'), 'Contexts are matched before names');
        $this->assertSame(2, $device->vrfIdForContext('blue'));
        $this->assertNull($device->vrfIdForContext('green'));
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: string|null, 3?: bool, 4?: bool}>  $vrfs  id, name, context, verified, enabled
     */
    private function device(array $vrfs): Device
    {
        $device = new Device;
        $device->setRelation('vrfs', new Collection(array_map(fn (array $vrf) => (new Vrf)->forceFill([
            'vrf_id' => $vrf[0],
            'vrf_name' => $vrf[1],
            'context_name' => $vrf[2],
            'context_verified' => $vrf[3] ?? true,
            'context_enabled' => $vrf[4] ?? true,
        ]), $vrfs)));

        return $device;
    }
}
