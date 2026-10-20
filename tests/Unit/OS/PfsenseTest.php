<?php

namespace LibreNMS\Tests\Unit\OS;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\OS;
use LibreNMS\Tests\Mocks\SnmprecSnmpBackend;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

class PfsenseTest extends TestCase
{
    /** @var list<string> */
    private array $walks = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Exercise the real discovery code with fixture-backed GET responses.
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $fixtures = new SnmprecSnmpBackend;
        $backend->shouldReceive('get')->andReturnUsing($fixtures->get(...));
        $backend->shouldReceive('walk')->andReturnUsing(function (string $target, string $oid, ...$args) use ($fixtures) {
            $this->walks[] = $oid;

            return $fixtures->walk($target, $oid, ...$args);
        });
        $backend->shouldNotReceive('next');
        $this->app->instance(SnmpBackendInterface::class, $backend);
    }

    protected function tearDown(): void
    {
        DeviceCache::flush();
        parent::tearDown();
    }

    private function device(string $description, string $fixture = 'pfsense_net-snmp-default'): Device
    {
        $device = new Device([
            'hostname' => '127.0.0.1',
            'overwrite_ip' => null,
            'transport' => 'udp',
            'port' => 161,
            'snmpver' => 'v2c',
            'community' => $fixture,
            'os' => 'pfsense',
            'sysDescr' => $description,
            'sysObjectID' => '.1.3.6.1.4.1.8072.3.2.8',
        ]);
        $device->device_id = 1;
        $device->setRelation('attribs', new Collection);
        DeviceCache::fake($device);
        DeviceCache::setPrimary(1);

        return $device;
    }

    private function discover(Device $device): void
    {
        $data = $device->toArray();
        OS::make($data)->discoverOS($device);
    }

    #[DataProvider('versions')]
    public function testVersionAndArchitectureFromSysDescr(string $description, string $version, string $hardware): void
    {
        $device = $this->device($description);
        $this->discover($device);

        $this->assertSame($version, $device->version);
        $this->assertSame($hardware, $device->hardware);
    }

    /** @return array<string, array{string, string, string}> */
    public static function versions(): array
    {
        return [
            'native CE' => ['pfSense 2.3.2-RELEASE pfSense FreeBSD 10.3-RELEASE-p5 amd64', '2.3.2-RELEASE', 'amd64'],
            'NET-SNMP CE' => ['pfSensefwexample.test 2.8.1-RELEASE FreeBSD 15.0-CURRENT amd64', '2.8.1-RELEASE', 'amd64'],
            'NET-SNMP Plus' => ['pfSense Plusfwexample.test 26.03-RELEASE FreeBSD 15.0-CURRENT amd64', '26.03-RELEASE', 'amd64'],
            'reported NET-SNMP description' => ['pfSensepfSenseexample.test 26.07-RELEASE FreeBSD 16.0-CURRENT amd64', '26.07-RELEASE', 'amd64'],
            'patch release' => ['pfSense Plusfwexample.test 24.03_1 FreeBSD 14.0-CURRENT amd64', '24.03_1', 'amd64'],
            'ARM' => ['pfSense Plusfwexample.test 25.11-RELEASE FreeBSD 15.0-CURRENT aarch64', '25.11-RELEASE', 'aarch64'],
        ];
    }

    public function testUpgradeAndDowngradeRefreshExistingVersion(): void
    {
        $device = $this->device('');
        $device->version = '24.11';
        foreach (['25.11', '26.03', '25.11'] as $version) {
            $device->sysDescr = "pfSense Plusfwexample.test $version FreeBSD 15.0-CURRENT amd64";
            $this->discover($device);
            $this->assertSame($version, $device->version);
        }
    }

    public function testCustomDescriptionDoesNotInventAVersionOrHardware(): void
    {
        $device = $this->device('Netgate pfSense Plus');
        $this->discover($device);

        $this->assertNull($device->version);
        $this->assertNull($device->hardware);
    }

    public function testCpuDescriptionDoesNotOverwriteArchitecture(): void
    {
        $device = $this->device('pfSense 2.3.2-RELEASE pfSense FreeBSD 10.3-RELEASE-p5 amd64', 'pfsense');
        $this->discover($device);

        $this->assertSame('amd64', $device->hardware);
    }

    public function testExistingHardwareExtendOverridesArchitectureAndStaleHardware(): void
    {
        $device = $this->device('pfSense Plusfwexample.test 26.03 FreeBSD 15.0-CURRENT amd64', 'pfsense_net-snmp-identity');
        $device->hardware = 'Old appliance';
        $this->discover($device);

        $this->assertSame('Netgate Test Appliance', $device->hardware);
        $this->assertSame('26.03', $device->version);
    }

    public function testIdentityDoesNotWalkPackageOrProcessorTables(): void
    {
        $device = $this->device('pfSense Plusfwexample.test 26.03 FreeBSD 15.0-CURRENT amd64');
        $this->discover($device);

        $this->assertSame([], $this->walks);
    }
}
