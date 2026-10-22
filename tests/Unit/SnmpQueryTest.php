<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpTranslatorInterface;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Enum\SnmpQuickPrint;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Tests\TestCase;
use Mockery;

class SnmpQueryTest extends TestCase
{
    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = $this->makeDeviceWithSnmpConfig();
    }

    private function makeDeviceWithSnmpConfig(array $secretData = [], array $settings = [], array $deviceAttrs = []): Device
    {
        $device = new Device(array_merge(['hostname' => '10.1.2.3'], $deviceAttrs));
        $device->device_id = 1;
        $device->setRelation('attribs', new Collection);

        $secret = new Secret([
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => array_merge(['version' => 'v2c', 'community' => 'test-community'], $secretData),
        ]);
        $method = new DevicePollingMethod([
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
            'settings' => array_merge(['timeout' => 2, 'retries' => 1], $settings),
        ]);
        $method->setRelation('device', $device);
        $method->setRelation('secret', $secret);
        $device->setRelation('pollingMethods', collect([$method]));

        return $device;
    }

    private function mockBackend(): Mockery\MockInterface&SnmpBackendInterface
    {
        return Mockery::mock(SnmpBackendInterface::class);
    }

    public function testGetDelegatesToBackend(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options) => $target === '10.1.2.3'
                && $config->community === 'test-community'
                && $oids === ['sysDescr.0']
                && $options->oidFormat === SnmpOidOutput::Numeric
                && $options->context === '')
            ->andReturn(new SnmpResponse(['sysDescr.0' => 'Linux 6.0']));

        $query = (new SnmpQueryBuilder($mockBackend))
            ->device($this->device)
            ->numeric();

        $response = $query->get('sysDescr.0');

        $this->assertSame(['sysDescr.0' => 'Linux 6.0'], $response->values());
    }

    public function testWalkDelegatesForEachOid(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (string $target, string $oid) => $oid === 'ifDescr')
            ->andReturn(new SnmpResponse(['ifDescr.1' => 'eth0']));

        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (string $target, string $oid) => $oid === 'ifType')
            ->andReturn(new SnmpResponse(['ifType.1' => 'ethernetCsmacd']));

        $query = (new SnmpQueryBuilder($mockBackend))->device($this->device);
        $response = $query->walk(['ifDescr', 'ifType']);

        $this->assertSame(['ifDescr.1' => 'eth0', 'ifType.1' => 'ethernetCsmacd'], $response->values());
    }

    public function testNextDelegatesToBackend(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('next')
            ->once()
            ->withArgs(fn (string $target, array $oids) => $oids === ['sysDescr.0'])
            ->andReturn(new SnmpResponse(['sysObjectID.0' => '.1.3.6.1.4.1']));

        $query = (new SnmpQueryBuilder($mockBackend))->device($this->device);
        $response = $query->next('sysDescr.0');

        $this->assertSame(['sysObjectID.0' => '.1.3.6.1.4.1'], $response->values());
    }

    public function testLimitOidsChunksRequests(): void
    {
        $device = $this->makeDeviceWithSnmpConfig(settings: ['max_oid' => 2]);

        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (string $target, array $oids) => $oids === ['oid1', 'oid2'])
            ->andReturn(new SnmpResponse(['oid1' => '1', 'oid2' => '2']));

        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (string $target, array $oids) => $oids === ['oid3'])
            ->andReturn(new SnmpResponse(['oid3' => '3']));

        $query = (new SnmpQueryBuilder($mockBackend))->device($device);
        $response = $query->get(['oid1', 'oid2', 'oid3']);

        $this->assertSame(['oid1' => '1', 'oid2' => '2', 'oid3' => '3'], $response->values());
    }

    public function testAbortOnFailure(): void
    {
        $mockBackend = $this->mockBackend();
        // First OID fails (exitCode 1)
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (string $target, string $oid) => $oid === 'unsupportedOid')
            ->andReturn(new SnmpResponse([], 'Timeout: No Response', 1));

        // Second OID should never be queried because of abortOnFailure
        $mockBackend->shouldNotReceive('walk')
            ->withArgs(fn (string $target, string $oid) => $oid === 'secondOid');

        $query = (new SnmpQueryBuilder($mockBackend))
            ->device($this->device)
            ->abortOnFailure();

        $response = $query->walk(['unsupportedOid', 'secondOid']);

        $this->assertFalse($response->isValid());
    }

    public function testCachePreventsDuplicateBackendCall(): void
    {
        Cache::flush();

        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (string $target, array $oids) => $oids === ['sysDescr.0'])
            ->andReturn(new SnmpResponse(['sysDescr.0' => 'CachedLinux']));

        $query = (new SnmpQueryBuilder($mockBackend))
            ->device($this->device)
            ->cache();

        $first = $query->get('sysDescr.0');
        $second = $query->get('sysDescr.0');

        $this->assertSame(['sysDescr.0' => 'CachedLinux'], $first->values());
        $this->assertSame(['sysDescr.0' => 'CachedLinux'], $second->values());
    }

    public function testTranslateDelegatesToTranslateBackend(): void
    {
        $mockBackend = $this->mockBackend();
        $mockTranslate = Mockery::mock(SnmpTranslatorInterface::class);
        $mockTranslate->shouldReceive('translate')
            ->once()
            ->withArgs(fn (string $oid, SnmpQueryOptions $options) => $oid === 'IF-MIB::ifTable')
            ->andReturn('.1.3.6.1.2.1.2.2');

        $query = (new SnmpQueryBuilder($mockBackend, $mockTranslate))
            ->device($this->device)
            ->numeric();

        $result = $query->translate('IF-MIB::ifTable');
        $this->assertSame('.1.3.6.1.2.1.2.2', $result);
    }

    public function testContextV3Prefix(): void
    {
        $v3Device = $this->makeDeviceWithSnmpConfig(
            secretData: [
                'version' => 'v3',
                'authlevel' => 'noAuthNoPriv',
                'authname' => null,
            ],
            deviceAttrs: ['device_id' => 2],
        );

        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options) => $options->context === 'vlan-100')
            ->andReturn(new SnmpResponse(['val' => '1']));

        $query = (new SnmpQueryBuilder($mockBackend))
            ->device($v3Device)
            ->context('100', 'vlan-');

        $query->get('val');
    }

    public function testOptionsConvertsNetSnmpFlagsToQueryOptions(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (string $target, string $oid, SnmpConfig $config, SnmpQueryOptions $options) => $options->numericIndexes === true
                && $options->oidFormat === SnmpOidOutput::Suffix
                && $options->numericEnums === false
                && $options->tolerateUnorderedIndexes === true)
            ->andReturn(new SnmpResponse(['test' => '1']));

        $query = (new SnmpQueryBuilder($mockBackend))
            ->device($this->device)
            ->options(['-OQUsb', '-Cc']);

        $response = $query->walk('test');
        $this->assertSame(['test' => '1'], $response->values());
    }

    public function testSnmpQueryDispatchesSnmpQueryExecutedEvent(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\SnmpQueryExecuted::class]);

        $mockBackend = Mockery::mock(SnmpBackendInterface::class);
        $mockBackend->shouldReceive('get')
            ->once()
            ->andReturn(new SnmpResponse(['sysDescr.0' => 'Linux 6.0'], command: ['/usr/bin/snmpget', 'sysDescr.0']));

        $query = (new SnmpQueryBuilder($mockBackend))->device($this->device);
        $query->get('sysDescr.0');

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\SnmpQueryExecuted::class, fn (\App\Events\SnmpQueryExecuted $event) => $event->target === $this->device->pollerTarget()
            && $event->method === 'snmpget'
            && $event->oids === ['sysDescr.0']
            && $event->duration >= 0
            && $event->device === $this->device
            && $event->response->command === ['/usr/bin/snmpget', 'sysDescr.0']
            && $event->response->values() === ['sysDescr.0' => 'Linux 6.0']);
    }

    public function testSnmpQueryDefaultsToQuickPrintOptions(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options) => $options->quickPrint === SnmpQuickPrint::Equals
                    && $options->extendedIndex === true
                    && $options->printUnits === false
                    && $options->numericEnums === true
                    && $options->numericTimeticks === true)
            ->andReturn(new SnmpResponse(['sysDescr.0' => 'Linux']));

        $query = (new SnmpQueryBuilder($mockBackend))->device($this->device);
        $query->get('sysDescr.0');
    }

    public function testHideMibSetsSuffixOidFormat(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (string $target, array $oids, SnmpConfig $config, SnmpQueryOptions $options) => $options->oidFormat === SnmpOidOutput::Suffix)
            ->andReturn(new SnmpResponse(['sysDescr.0' => 'Linux']));

        $query = (new SnmpQueryBuilder($mockBackend))->device($this->device)->hideMib();
        $query->get('sysDescr.0');
    }

    public function testPrepareOptionsDisablesBulkForNoBulkOids(): void
    {
        $this->device->os = 'generic';
        \App\Facades\LibrenmsConfig::set('os.generic.oids.no_bulk', ['UCD-SNMP-MIB::laLoadInt']);

        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (string $target, string $oid, SnmpConfig $config, SnmpQueryOptions $options) => $options->allowBulk === false)
            ->andReturn(new SnmpResponse(['laLoadInt' => '1']));

        $query = (new SnmpQueryBuilder($mockBackend))->device($this->device);
        $query->walk('UCD-SNMP-MIB::laLoadInt');
    }
}
