<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Models\Package;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\Packages;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
use LibreNMS\Tests\InMemoryDbTestCase;
use LibreNMS\Tests\Traits\FakesPollingMethods;
use LibreNMS\Util\ModuleList;
use Mockery;

final class PackagesTest extends InMemoryDbTestCase
{
    use FakesPollingMethods;

    public function testDiscoversHostResourcesInstalledPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1002, 'name' => 'python2.7-minimal_2.7.18-13ubuntu1.5_amd64'],
            ['index' => 1004, 'name' => 'python3-apport_2.28.3-0ubuntu0.1_all'],
            ['index' => 1215, 'name' => 'xfonts-utils_1:7.7+6build3_amd64'],
        ]);

        $module = new Packages;
        $this->assertTrue($module->shouldDiscover($os));
        $module->discover($os);

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['python2.7-minimal', 'python3-apport', 'xfonts-utils'], $packages->pluck('name')->all());
        $this->assertSame(['2.7.18-13ubuntu1.5', '2.28.3-0ubuntu0.1', '1:7.7+6build3'], $packages->pluck('version')->all());
        $this->assertSame(['amd64', 'all', 'amd64'], $packages->pluck('arch')->all());
        $this->assertSame(['host-res', 'host-res', 'host-res'], $packages->pluck('manager')->all());
        $this->assertSame([PollingMethodType::Snmp, PollingMethodType::Snmp, PollingMethodType::Snmp], $packages->pluck('polling_method')->all());
        $this->assertTrue($packages->every(fn (Package $package): bool => $package->size === null && $package->build === ''));
    }

    public function testPollsRedHatNamedPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 869, 'name' => 'libsndfile-1.0.31-10.el9'],
            ['index' => 1110, 'name' => 'bubblewrap-0.6.3-1.el9_9.1'],
            ['index' => 1210, 'name' => 'qt5-qt3d-5.15.9-3.el9_9.1'],
        ]);

        $module = new Packages;
        $this->assertTrue($module->shouldPoll($os));
        $module->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['bubblewrap', 'libsndfile', 'qt5-qt3d'], $packages->pluck('name')->all());
        $this->assertSame(['0.6.3', '1.0.31', '5.15.9'], $packages->pluck('version')->all());
        $this->assertSame(['1.el9_9.1', '10.el9', '3.el9_9.1'], $packages->pluck('build')->all());
        $this->assertSame(['unknown', 'unknown', 'unknown'], $packages->pluck('arch')->all());
    }

    public function testSplitsTheRpmEpochFromTheVersion(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => '1:bash-5.1.8-6.el9'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $package = Package::where('device_id', $os->getDeviceId())->sole();
        $this->assertSame('bash', $package->name);
        $this->assertSame('1:5.1.8', $package->version);
        $this->assertSame('6.el9', $package->build);
    }

    public function testDecodesHexEncodedPackageNames(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => '74 65 78 6C 69 76 65 2D 63 6F 6C 6C 65 63 74 69 6F 6E 2D 64 6F 63 75 6D 65 6E 74 61 74 69 6F 6E 2D 62 61 73 65 2D 73 76 6E 31 00 00 00 00 00 00 00 00 00 00 00 00', 'date' => '2026-11-12,6:6:22.0,+1:0'],
            ['index' => 2, 'name' => '6c 69 62 73 6e 64 66 69 6c 65 2d 31 2e 30 2e 33 31 2d 31 30 2e 65 6c 39 00 00 00 00 00 00'],
            ['index' => 3, 'name' => '70 79 74 68 6F 6E 32 2D 73 65 74 75 70 74 6F 6F 6C 73 2D 77 68 65 65 6C 2D 33 39 2E 30 2E 31 2D 31 34 2E 6D 6F 64 75 6C 65 5F 00 00 00 00 00 00'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['libsndfile', 'python2-setuptools-wheel', 'texlive-collection-documentation-base-svn1'], $packages->pluck('name')->all());
        $this->assertSame(['1.0.31', '39.0.1', '2026-11-12 06:06:22'], $packages->pluck('version')->all());
        $this->assertSame(['10.el9', '14.module_', ''], $packages->pluck('build')->all());
        $this->assertSame(['unknown', 'unknown', 'unknown'], $packages->pluck('arch')->all());
    }

    public function testDecodesHexEncodedNamesWrappedAcrossLines(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        // snmpwalk wraps long values across lines (a newline after every 16 bytes)
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => "74 65 78 6C 69 76 65 2D 63 6F 6C 6C 65 63 74 69 \n6F 6E 2D 66 6F 6E 74 73 72 65 63 6F 6D 6D 65 6E \n64 65 64 2D 73 76 6E 32 38 30 00 00 00 00 00 00", 'date' => '2020-05-26,6:13:7.0,+2:0'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $package = Package::where('device_id', $os->getDeviceId())->sole();
        $this->assertSame('texlive-collection-fontsrecommended-svn280', $package->name);
        $this->assertSame('2020-05-26 06:13:07', $package->version);
        $this->assertSame('unknown', $package->arch);
    }

    public function testKeepsALoneTwoDigitPackageNameAsPlainText(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        // "bc" is a plain two-character name, not a single hex byte
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => 'bc', 'date' => '2025-6-18,7:36:25.0,+2:0'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $package = Package::where('device_id', $os->getDeviceId())->sole();
        $this->assertSame('bc', $package->name);
        $this->assertSame('2025-06-18 07:36:25', $package->version);
        $this->assertSame('unknown', $package->arch);
    }

    public function testNameWithoutVersionFallsBackToTheModificationDate(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => 'vim', 'date' => '2025-6-18,7:36:25.0,+2:0'],
            ['index' => 2, 'name' => 'less', 'date' => '+02:00 2026-10-21:14:38:02.0'],
            ['index' => 3, 'name' => 'grep', 'date' => '2024-1-5,3:2:1.0'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['2024-01-05 03:02:01', '2026-10-21 14:38:02', '2025-06-18 07:36:25'], $packages->pluck('version')->all());
        $this->assertSame(['unknown', 'unknown', 'unknown'], $packages->pluck('arch')->all());
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
            'polling_method' => PollingMethodType::Snmp,
        ]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'removed',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '1.0.0',
            'polling_method' => PollingMethodType::Snmp,
        ]);

        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => 'bash_5.2.37_amd64'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->get();
        $this->assertSame(['bash'], $packages->pluck('name')->all());
        $this->assertSame('5.2.37', $packages->sole()->version);
    }

    public function testKeepsBothVersionsOfTheSamePackageInstalled(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => 'bash_5.1.0_amd64'],
            ['index' => 2, 'name' => 'bash_5.2.0_amd64'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('version')->get();
        $this->assertSame(['bash', 'bash'], $packages->pluck('name')->all());
        $this->assertSame(['5.1.0', '5.2.0'], $packages->pluck('version')->all());
        $this->assertSame(['amd64', 'amd64'], $packages->pluck('arch')->all());
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
            'polling_method' => PollingMethodType::Snmp,
        ]);

        $this->fakeSnmpInstalledPackages([]);

        (new Packages)->poll($os, $this->datastore());

        $this->assertSame(1, Package::where('device_id', $os->getDeviceId())->count());
    }

    public function testKeepsExistingPackagesWhenTheWalkTimesOut(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'bash',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '5.2.0',
            'polling_method' => PollingMethodType::Snmp,
        ]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'vim',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '9.0.0',
            'polling_method' => PollingMethodType::Snmp,
        ]);

        // the walk returned only part of the table before the device timed out
        $partial = "HOST-RESOURCES-MIB::hrSWInstalledIndex.1 = 1\nHOST-RESOURCES-MIB::hrSWInstalledName.1 = \"bash_5.2.0_amd64\"\nHOST-RESOURCES-MIB::hrSWInstalledID.1 = 0.0\nHOST-RESOURCES-MIB::hrSWInstalledType.1 = 4";
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->zeroOrMoreTimes()->andReturnUsing(fn (): RawSnmpResponse => $this->snmpProbeResponse());
        $backend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (string $target, string $oid): bool => $oid === 'HOST-RESOURCES-MIB::hrSWInstalledTable')
            ->andReturn(new RawSnmpResponse($partial, 'Timeout: No Response from 10.0.0.1: 1 (No answer from 10.0.0.1)', 1));

        $this->app->instance(SnmpBackendInterface::class, $backend);
        $this->app->instance(SnmpPollingMethod::class, new SnmpPollingMethod($backend));

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['bash', 'vim'], $packages->pluck('name')->all());
    }

    public function testDiscoversPackagesFromTheAgentOutput(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, [
            'output' => "<<<rpm>>>\nbash 5.2.0 3 x86_64 12345\nkernel 6.8.0 1 x86_64 67890\n",
            'time' => 15,
        ]);

        $module = new Packages;
        $this->assertTrue($module->shouldDiscover($os));
        $module->discover($os);

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['bash', 'kernel'], $packages->pluck('name')->all());
        $bash = $packages->firstWhere('name', 'bash');
        $this->assertSame('5.2.0', $bash->version);
        $this->assertSame('3', (string) $bash->build);
        $this->assertSame('x86_64', $bash->arch);
        $this->assertSame('rpm', $bash->manager);
        $this->assertSame(12345, (int) $bash->size);
        $this->assertSame(PollingMethodType::UnixAgent, $bash->polling_method);
    }

    public function testUnixAgentPackagesAreDiscoveredByDefaultAndPollingRequiresOptIn(): void
    {
        $device = $this->makeOs([PollingMethodType::UnixAgent])->getDevice();
        $modules = new ModuleList;

        $this->assertTrue($modules->modulesWithStatus(ProcessType::Discovery, $device)['packages']->isEnabled());
        $this->assertFalse($modules->modulesWithStatus(ProcessType::Poller, $device)['packages']->isEnabled());

        $device->setAttrib('poll_packages', true);
        $this->assertTrue($modules->modulesWithStatus(ProcessType::Poller, $device)['packages']->isEnabled());
    }

    public function testAgentPackagesReplaceSnmpPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent]);
        // a package collected earlier by snmp
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'bash',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '5.2.0',
            'polling_method' => PollingMethodType::Snmp,
        ]);
        $this->fakeMethod(UnixAgentPollingMethod::class, true, [
            'output' => "<<<rpm>>>\nbash 5.2.0 3 x86_64 12345\n",
            'time' => 15,
        ]);

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->get();
        $this->assertSame(['bash'], $packages->pluck('name')->all());
        $this->assertSame('rpm', $packages->sole()->manager);
        $this->assertSame(PollingMethodType::UnixAgent, $packages->sole()->polling_method);
    }

    public function testKeepsExistingPackagesWhenTheAgentTimesOut(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent, PollingMethodType::Snmp]);
        // packages collected by a previous complete agent fetch
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'bash',
            'manager' => 'rpm',
            'arch' => 'x86_64',
            'version' => '5.2.0',
            'polling_method' => PollingMethodType::UnixAgent,
        ]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'kernel',
            'manager' => 'rpm',
            'arch' => 'x86_64',
            'version' => '6.8.0',
            'polling_method' => PollingMethodType::UnixAgent,
        ]);

        // the fetch returned a partial list and then timed out
        $this->fakeMethod(UnixAgentPollingMethod::class, true, [
            'output' => "<<<rpm>>>\nbash 5.2.0 3 x86_64 12345\n",
            'time' => 15,
            'timed_out' => true,
        ]);
        // snmp has a nonempty subset, which must not replace the agent inventory after a timeout
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->zeroOrMoreTimes()->andReturnUsing(fn (): RawSnmpResponse => $this->snmpProbeResponse());
        $backend->shouldReceive('walk')->zeroOrMoreTimes()->andReturn(new RawSnmpResponse(
            "HOST-RESOURCES-MIB::hrSWInstalledIndex.1 = 1\nHOST-RESOURCES-MIB::hrSWInstalledName.1 = \"host-res-pkg_1.0.0_amd64\"\nHOST-RESOURCES-MIB::hrSWInstalledID.1 = 0.0\nHOST-RESOURCES-MIB::hrSWInstalledType.1 = 4",
            '', 0
        ));
        $this->app->instance(SnmpBackendInterface::class, $backend);
        $this->app->instance(SnmpPollingMethod::class, new SnmpPollingMethod($backend));

        (new Packages)->poll($os, $this->datastore());

        $packages = Package::where('device_id', $os->getDeviceId())->orderBy('name')->get();
        $this->assertSame(['bash', 'kernel'], $packages->pluck('name')->all());
        $this->assertSame([PollingMethodType::UnixAgent, PollingMethodType::UnixAgent], $packages->pluck('polling_method')->all());
    }

    public function testFallsBackToSnmpWhenTheAgentHasNoPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::UnixAgent, PollingMethodType::Snmp]);
        // the agent is reachable and complete, but it has no package section
        $this->fakeMethod(UnixAgentPollingMethod::class, true, [
            'output' => "<<<mem>>>\nMemTotal: 1000 kB\n",
            'time' => 15,
        ]);
        $this->fakeSnmpInstalledPackages([
            ['index' => 1, 'name' => 'bash_5.2.0_amd64'],
        ]);

        (new Packages)->poll($os, $this->datastore());

        $package = Package::where('device_id', $os->getDeviceId())->sole();
        $this->assertSame('bash', $package->name);
        $this->assertSame(PollingMethodType::Snmp, $package->polling_method);
    }

    public function testDataExists(): void
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
            'polling_method' => PollingMethodType::UnixAgent,
        ]);
        $this->assertTrue($module->dataExists($os->getDevice()));
    }

    public function testRemoveMethodData(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'snmp-pkg',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '1.0.0',
            'polling_method' => PollingMethodType::Snmp,
        ]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'agent-pkg',
            'manager' => 'rpm',
            'arch' => 'amd64',
            'version' => '2.0.0',
            'polling_method' => PollingMethodType::UnixAgent,
        ]);

        (new Packages)->removeMethodData($os->getDevice(), PollingMethodType::UnixAgent);

        $packages = Package::where('device_id', $os->getDeviceId())->get();
        $this->assertSame(['snmp-pkg'], $packages->pluck('name')->all());
        $this->assertSame(PollingMethodType::Snmp, $packages->sole()->polling_method);
    }

    public function testCleanupRemovesAllPackages(): void
    {
        $os = $this->makeOs([PollingMethodType::Snmp, PollingMethodType::UnixAgent]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'snmp-pkg',
            'manager' => 'host-res',
            'arch' => 'amd64',
            'version' => '1.0.0',
            'polling_method' => PollingMethodType::Snmp,
        ]);
        Package::factory()->create([
            'device_id' => $os->getDeviceId(),
            'name' => 'agent-pkg',
            'manager' => 'rpm',
            'arch' => 'amd64',
            'version' => '2.0.0',
            'polling_method' => PollingMethodType::UnixAgent,
        ]);

        $this->assertSame(2, (new Packages)->cleanup($os->getDevice()));

        $this->assertSame(0, Package::where('device_id', $os->getDeviceId())->count());
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

    private function snmpProbeResponse(): RawSnmpResponse
    {
        return new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1.3.6.1.1.1.1', '', 0);
    }

    private function datastore(): DataStorageInterface
    {
        return Mockery::mock(DataStorageInterface::class);
    }
}
