<?php

/**
 * MefTest.php
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
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\MefInfo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Mef;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;

final class MefTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testDiscoverySyncsEvcs(): void
    {
        $os = $this->os('generic');
        MefInfo::factory()->for($os->getDevice())->create(['mefID' => 9]);

        $this->mockWalk(new SnmpResponse([
            ...$this->evc(1, 'EVC-A', 1522),
            ...$this->evc(2, 'EVC-B', 0),
        ]));
        (new Mef)->discover($os);

        $mefs = $os->getDevice()->mefInfo()->orderBy('mefID')->get();
        $this->assertSame([1, 2], $mefs->pluck('mefID')->all());
        $this->assertSame(['EVC-A', 'EVC-B'], $mefs->pluck('mefIdent')->all());
        $this->assertSame([1522, 0], $mefs->pluck('mefMTU')->all(), 'Only Coriant corrects a zero MTU');
    }

    public function testDiscoveryKeepsEvcsWhenTheDeviceDoesNotAnswer(): void
    {
        $os = $this->os('generic');
        MefInfo::factory()->for($os->getDevice())->create(['mefID' => 1]);

        $this->mockWalk(new SnmpResponse([], 'Timeout: No Response from udp:192.0.2.1:161', 1));
        (new Mef)->discover($os);

        $this->assertSame([1], $os->getDevice()->mefInfo()->pluck('mefID')->all());
    }

    public function testPollingUpdatesEvcsAndLogsChanges(): void
    {
        $os = $this->os('generic');
        $mef = MefInfo::factory()->for($os->getDevice())->create(['mefID' => 1, 'mefIdent' => 'EVC-A', 'mefMTU' => 1522, 'mefAdmState' => 'unlocked']);
        $gone = MefInfo::factory()->for($os->getDevice())->create(['mefID' => 2, 'mefMTU' => 9000]);

        $this->mockWalk(new SnmpResponse($this->evc(1, 'EVC-A', 9100, 'locked')));
        (new Mef)->poll($os, Mockery::mock(DataStorageInterface::class));

        $this->assertSame(9100, $mef->fresh()->mefMTU);
        $this->assertSame('locked', $mef->fresh()->mefAdmState);
        $this->assertSame(9000, $gone->fresh()->mefMTU, 'EVCs no longer reported are left alone');
        $this->assertEqualsCanonicalizing(
            ['MEF Link : EVC-A (1522) -> 9100', 'MEF Link : EVC-A (unlocked) -> locked'],
            Eventlog::where('device_id', $os->getDevice()->device_id)->where('message', 'like', 'MEF Link : %')->pluck('message')->all(),
        );
    }

    public function testCoriantCorrectsZeroMtu(): void
    {
        $os = $this->os('coriant');

        $this->mockWalk(new SnmpResponse([
            ...$this->evc(1, 'EVC-A', 0),
            ...$this->evc(2, 'EVC-B', 1522),
        ]));
        (new Mef)->discover($os);

        $mefs = $os->getDevice()->mefInfo()->orderBy('mefID')->get();
        $this->assertSame([1600, 1522], $mefs->pluck('mefMTU')->all());

        $this->mockWalk(new SnmpResponse([
            ...$this->evc(1, 'EVC-A', 0),
            ...$this->evc(2, 'EVC-B', 0),
        ]));
        (new Mef)->poll($os, Mockery::mock(DataStorageInterface::class));

        $this->assertSame([1600, 1600], $os->getDevice()->mefInfo()->orderBy('mefID')->pluck('mefMTU')->all());
    }

    private function os(string $os): OS
    {
        $device = Device::factory()->create(['os' => $os]);
        DeviceCache::setPrimary($device->device_id);
        $deviceArray = DeviceCache::refresh($device->device_id)->toArray();

        return OS::make($deviceArray);
    }

    private function mockWalk(SnmpResponse $response): void
    {
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('mibs', 'cache', 'enumStrings', 'abortOnFailure')->andReturnSelf();
        $query->shouldReceive('walk')->andReturn($response);
        $this->app->instance(SnmpQueryBuilder::class, $query);
    }

    /**
     * @return array<string, string>
     */
    private function evc(int $mefID, string $ident, int $mtu, string $adminState = 'unlocked'): array
    {
        return [
            "MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType.$mefID" => 'epl',
            "MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier.$mefID" => $ident,
            "MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize.$mefID" => (string) $mtu,
            "MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState.$mefID" => $adminState,
            "MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus.$mefID" => 'active',
        ];
    }
}
