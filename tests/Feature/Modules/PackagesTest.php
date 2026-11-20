<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Models\Package;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Packages;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;
use Mockery;

final class PackagesTest extends InMemoryDbTestCase
{
    use FakesPollingMethods;

    public function testPollsHostResourcesInstalledPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1002, 'name' => 'python2.7-minimal_2.7.18-13ubuntu1.5_amd64'],
            ['index' => 1004, 'name' => 'python3-apport_2.28.3-0ubuntu0.1_all'],
            ['index' => 1215, 'name' => 'xfonts-utils_1:7.7+6build3_amd64'],
        ]);

        $module = new Packages;
        $this->assertFalse($module->shouldDiscover($os));
        $this->assertTrue($module->shouldPoll($os));
        $module->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['python2.7-minimal', 'python3-apport', 'xfonts-utils'], $packages->pluck('name')->all());
        $this->assertSame(['2.7.18-13ubuntu1.5', '2.28.3-0ubuntu0.1', '1:7.7+6build3'], $packages->pluck('version')->all());
        $this->assertSame(['amd64', 'all', 'amd64'], $packages->pluck('arch')->all());
        $this->assertSame(['host-res', 'host-res', 'host-res'], $packages->pluck('manager')->all());
        $this->assertTrue($packages->every(fn (Package $package): bool => $package->size === null && $package->build === '' && (bool) $package->status));
    }

    public function testNameWithoutVersionFallsBackToTheModificationDate(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => 'vim', 'date' => '2025-6-18,7:36:25.0,+2:0'],
            ['index' => 2, 'name' => 'less', 'date' => '+02:00 2026-10-21:14:38:02.0'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['2026-10-21 14:38:02', '2025-06-18 07:36:25'], $packages->pluck('version')->all());
        $this->assertSame(['unknown', 'unknown'], $packages->pluck('arch')->all());
    }

    public function testUpdatesChangedVersionsAndRemovesMissingPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'bash',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '5.2.0',
        ]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'removed',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '1.0.0',
        ]);

        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => 'bash_5.2.37_amd64'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->get();
        $this->assertSame(['bash'], $packages->pluck('name')->all());
        $this->assertSame('5.2.37', $packages->sole()->version);
    }

    public function testKeepsExistingPackagesWhenTheTableIsNotExposed(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'bash',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '5.2.0',
        ]);

        $this->fakeSnmpInstalledPackages([]);

        (new Packages)->poll($os, $this->datastore());

        $this->assertSame(1, Package::where('device_id', $os->getDeviceId())->count());
    }

    public function testIsNotPolledWhenTheUnixAgentIsEnabled(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        $this->fakeSnmpMethodAvailable();

        $this->assertFalse((new Packages)->shouldPoll($os));
    }

    public function testDataExistsOnlyForHostResourcesPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $module = new Packages;

        $this->assertFalse($module->dataExists($os->getDevice()));

        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'bash',
            'manager' => 'deb',
            'arch' => 'amd64',
            'version' => '5.2.0',
        ]);
        $this->assertFalse($module->dataExists($os->getDevice()));

        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'kernel',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '6.8.0',
        ]);
        $this->assertTrue($module->dataExists($os->getDevice()));
    }

    public function testCleanupRemovesOnlyHostResourcesPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'bash',
            'manager' => 'deb',
            'arch' => 'amd64',
            'version' => '5.2.0',
        ]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'kernel',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '6.8.0',
        ]);

        $this->assertSame(1, (new Packages)->cleanup($os->getDevice()));

        $this->assertSame(['bash'], Package::where('device_id', $os->getDeviceId())->pluck('name')->all());
    }

    /**
     * Bind an available snmp polling method whose snmp walk of hrSWInstalledTable returns these installed packages.
     *
     * @param  list<array{index?: int, name: string, date?: string}>  $packages
     */
    private function fakeSnmpInstalledPackages(array $packages): void
    {
        $lines = [];
        foreach ($packages as $package) {
            $i = $package['index'] ?? 1;
            $lines[] = "HOST-RESOURCES-MIB::hrSWInstalledIndex.$i = $i";
            $lines[] = "HOST-RESOURCES-MIB::hrSWInstalledName.$i = \"{$package['name']}\"";
            $lines[] = "HOST-RESOURCES-MIB::hrSWInstalledID.$i = 0.0";
            $lines[] = "HOST-RESOURCES-MIB::hrSWInstalledType.$i = 4";
            if (isset($package['date'])) {
                $lines[] = "HOST-RESOURCES-MIB::hrSWInstalledDate.$i = \"{$package['date']}\"";
            }
        }

        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->zeroOrMoreTimes()->andReturnUsing(fn (): RawSnmpResponse => $this->snmpProbeResponse());
        $backend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (string $target, string $oid): bool => $oid === 'HOST-RESOURCES-MIB::hrSWInstalledTable')
            ->andReturn(new RawSnmpResponse(implode("\n", $lines), '', 0));

        $this->app->instance(SnmpBackendInterface::class, $backend);
        $this->app->instance(SnmpPollingMethod::class, new SnmpPollingMethod($backend));
    }

    /**
     * Bind an available snmp polling method without any hrSWInstalledTable data.
     */
    private function fakeSnmpMethodAvailable(): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->zeroOrMoreTimes()->andReturnUsing(fn (): RawSnmpResponse => $this->snmpProbeResponse());

        $this->app->instance(SnmpBackendInterface::class, $backend);
        $this->app->instance(SnmpPollingMethod::class, new SnmpPollingMethod($backend));
    }

    private function snmpProbeResponse(): RawSnmpResponse
    {
        return new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1.3.6.1.1.1.1', '', 0);
    }

    private function datastore(): DataStorageInterface
    {
        return Mockery::mock(DataStorageInterface::class);
    }
}
