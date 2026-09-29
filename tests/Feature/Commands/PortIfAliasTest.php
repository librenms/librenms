<?php

/**
 * PortIfAliasTest.php
 *
 * Test lnms port:ifAlias: a stored ifAlias can come from the device, from a
 * user override, or from the automatic fallback that fills the field when the
 * device reports no ifAlias. The command must keep those apart.
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
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Feature\Commands;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Facades\Artisan;
use LibreNMS\Data\Source\Snmp\SnmpQueryInterface;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Tests\InMemoryDbTestCase;
use Mockery;
use RuntimeException;

final class PortIfAliasTest extends InMemoryDbTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * No snmp: every port is reported as "db only", the counters stay at zero
     * and the command must still exit cleanly.
     */
    public function testWithoutSnmpReportsDatabaseOnly(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'uplink to core', 'GigabitEthernet0/1');
        $this->makePort($device, 2, 'Gi0/2', 'cust: Acme [1Gbps] {C-1}', 'GigabitEthernet0/2');

        $this->artisan('port:ifAlias', ['device spec' => $device->hostname, '--no-snmp' => true])
            ->expectsOutputToContain('db only')
            ->assertExitCode(0);
    }

    /**
     * The device reports ifAlias and the stored value matches: source "device".
     */
    public function testDeviceValueIsReportedAsDevice(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'transit: provider [10Gbps]', 'GigabitEthernet0/1');

        $this->fakeSnmp(alias: [1 => 'transit: provider [10Gbps]']);
        $this->artisan('port:ifAlias', ['device spec' => $device->hostname])
            ->expectsOutputToContain('device')
            ->assertExitCode(0);
    }

    /**
     * A user override must be recognised as such and never as the automatic
     * fallback, even when the stored value happens to equal the device value.
     */
    public function testUserOverrideIsRecognised(): void
    {
        $device = $this->makeDevice();
        $port = $this->makePort($device, 1, 'Gi0/1', 'cust: Acme [1Gbps] {C-1}', 'GigabitEthernet0/1');
        $device->setAttrib('ifName:Gi0/1', '1');

        $this->fakeSnmp(alias: [1 => 'transit: provider [10Gbps]']);
        $this->artisan('port:ifAlias', ['device spec' => $device->hostname])
            ->expectsOutputToContain('override')
            ->assertExitCode(0);

        $this->assertSame('cust: Acme [1Gbps] {C-1}', $port->fresh()->ifAlias);
    }

    /**
     * The device reports no ifAlias: the stored value was copied from ifDescr
     * by port_fill_missing_and_trim(), which is not a user override.
     */
    public function testFillFromIfDescrIsNotAnOverride(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'GigabitEthernet0/1', 'GigabitEthernet0/1');

        $this->fakeSnmp(alias: [], descr: [1 => 'GigabitEthernet0/1']);
        $this->artisan('port:ifAlias', ['device spec' => $device->hostname])
            ->expectsOutputToContain('fill ifDescr')
            ->assertExitCode(0);
    }

    /**
     * Neither ifAlias nor ifDescr: the stored value was copied from ifName.
     */
    public function testFillFromIfName(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'Gi0/1', 'GigabitEthernet0/1');

        $this->fakeSnmp(alias: [], descr: [1 => 'GigabitEthernet0/1']);
        $this->artisan('port:ifAlias', ['device spec' => $device->hostname])
            ->expectsOutputToContain('fill ifName')
            ->assertExitCode(0);
    }

    /**
     * A device that reports no ifAlias and whose stored value matches neither
     * field is reported as unknown, not silently blamed on the fallback.
     */
    public function testUnrecognisedFillIsReportedAsUnknown(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'something else entirely', 'GigabitEthernet0/1');

        $this->fakeSnmp(alias: [], descr: [1 => 'GigabitEthernet0/1']);
        $this->artisan('port:ifAlias', ['device spec' => $device->hostname])
            ->expectsOutputToContain('fill ?')
            ->assertExitCode(0);
    }

    /**
     * An override wins over the device value, so the stored value is kept and
     * the two are reported as different.
     */
    public function testOverrideWithDifferentValueIsReportedDifferent(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'cust: Acme [1Gbps] {C-1}', 'GigabitEthernet0/1');
        $device->setAttrib('ifName:Gi0/1', '1');

        $this->fakeSnmp(alias: [1 => 'transit: provider [10Gbps]']);
        $this->artisan('port:ifAlias', ['device spec' => $device->hostname])
            ->expectsOutputToContain('DIFFERENT')
            ->assertExitCode(0);
    }

    /**
     * --override-only lists the overridden port and hides the others.
     */
    public function testOverrideOnlyFilter(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'cust: Acme [1Gbps] {C-1}', 'GigabitEthernet0/1');
        $this->makePort($device, 2, 'Gi0/2', 'transit: provider [10Gbps]', 'GigabitEthernet0/2');
        $device->setAttrib('ifName:Gi0/1', '1');

        $this->fakeSnmp(alias: [1 => 'x', 2 => 'transit: provider [10Gbps]'], descr: [1 => 'd', 2 => 'd']);

        $output = $this->runCommand($device->hostname, ['--override-only' => true, '--no-snmp' => true]);

        $this->assertStringContainsString('Gi0/1', $output);
        $this->assertStringNotContainsString('Gi0/2', $output);
    }

    /**
     * --diff hides the ports that match the device and keeps the others.
     */
    public function testDiffFilter(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'cust: Acme [1Gbps] {C-1}', 'GigabitEthernet0/1');
        $this->makePort($device, 2, 'Gi0/2', 'transit: provider [10Gbps]', 'GigabitEthernet0/2');
        $device->setAttrib('ifName:Gi0/1', '1');

        $this->fakeSnmp(alias: [1 => 'transit: provider [1Gbps]', 2 => 'transit: provider [10Gbps]'], descr: [1 => 'd', 2 => 'd']);

        $output = $this->runCommand($device->hostname, ['--diff' => true]);

        $this->assertStringContainsString('Gi0/1', $output);
        $this->assertStringNotContainsString('Gi0/2', $output);
    }

    /**
     * Deleted and disabled ports are hidden unless --inactive is given.
     */
    public function testInactivePortsAreHiddenByDefault(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'live port', 'GigabitEthernet0/1');
        $this->makePort($device, 2, 'Gi0/2', 'deleted port', 'GigabitEthernet0/2', ['deleted' => true]);
        $this->makePort($device, 3, 'Gi0/3', 'disabled port', 'GigabitEthernet0/3', ['disabled' => true]);

        $this->fakeSnmp(alias: [1 => 'a', 2 => 'b', 3 => 'c'], descr: [1 => 'd', 2 => 'd', 3 => 'd']);

        $hidden = $this->runCommand($device->hostname);
        $this->assertStringContainsString('Gi0/1', $hidden);
        $this->assertStringNotContainsString('Gi0/2', $hidden);
        $this->assertStringNotContainsString('Gi0/3', $hidden);

        $shown = $this->runCommand($device->hostname, ['--inactive' => true]);
        $this->assertStringContainsString('Gi0/2', $shown);
        $this->assertStringContainsString('Gi0/3', $shown);
    }

    /**
     * A failing snmp walk must not abort the command: the report is still
     * produced from the database.
     */
    public function testSnmpFailureFallsBackToDatabase(): void
    {
        $device = $this->makeDevice();
        $this->makePort($device, 1, 'Gi0/1', 'cust: Acme [1Gbps] {C-1}', 'GigabitEthernet0/1');

        $this->app->bind(SnmpQueryInterface::class, function () {
            $mock = Mockery::mock(SnmpQueryInterface::class);
            $mock->shouldReceive('hideMib')->andReturnSelf();
            $mock->shouldReceive('device')->andReturnSelf();
            $mock->shouldReceive('walk')->andThrow(new RuntimeException('snmp unreachable'));

            return $mock;
        });

        $this->artisan('port:ifAlias', ['device spec' => $device->hostname])
            ->expectsOutputToContain('cust: Acme [1Gbps] {C-1}')
            ->assertExitCode(0);
    }

    public function testUnknownDeviceFails(): void
    {
        $this->artisan('port:ifAlias', ['device spec' => 'this-device-does-not-exist-12345'])
            ->expectsOutputToContain('No device matched')
            ->assertExitCode(1);
    }

    private function makeDevice(): Device
    {
        return Device::factory()->create(['os' => 'generic']);
    }

    private function makePort(Device $device, int $ifIndex, string $ifName, ?string $ifAlias, ?string $ifDescr, array $extra = []): Port
    {
        return Port::factory()->create(array_merge([
            'device_id' => $device->device_id,
            'ifIndex' => $ifIndex,
            'ifName' => $ifName,
            'ifAlias' => $ifAlias,
            'ifDescr' => $ifDescr,
        ], $extra));
    }

    /**
     * Replace the snmp backend with canned responses. The keys mimic what
     * hideMib() produces: ifAlias.1 style, not IF-MIB::ifAlias.1.
     *
     * SnmpQueryInterface is what SnmpQuery::make() resolves, see
     * AppServiceProvider.
     */
    private function fakeSnmp(array $alias = [], array $descr = []): void
    {
        $this->app->bind(SnmpQueryInterface::class, function () use ($alias, $descr) {
            $mock = Mockery::mock(SnmpQueryInterface::class);
            $mock->shouldReceive('hideMib')->andReturnSelf();
            $mock->shouldReceive('device')->andReturnSelf();
            $mock->shouldReceive('walk')->andReturnUsing(function (array $oids) use ($alias, $descr) {
                $values = [];
                foreach ($oids as $oid) {
                    $source = $oid === 'ifAlias' ? $alias : $descr;
                    foreach ($source as $index => $value) {
                        $values["$oid.$index"] = $value;
                    }
                }

                return new SnmpResponse($values);
            });

            return $mock;
        });
    }

    private function runCommand(string $deviceSpec, array $options = []): string
    {
        // Artisan::call() is used instead of $this->artisan() because the
        // PendingCommand helper keeps the output for expectsOutput* assertions
        // and does not expose it
        $exit = Artisan::call('port:ifAlias', array_merge(['device spec' => $deviceSpec], $options));
        $this->assertSame(0, $exit, 'the command should exit cleanly');

        return Artisan::output();
    }
}
