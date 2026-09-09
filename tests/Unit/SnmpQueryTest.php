<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQuery;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpTranslatorInterface;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Tests\TestCase;
use Mockery;

class SnmpQueryTest extends TestCase
{
    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = new Device([
            'hostname' => '10.1.2.3',
            'snmpver' => 'v2c',
            'community' => 'test-community',
            'timeout' => 2,
            'retries' => 1,
        ]);
        $this->device->device_id = 1;
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
            ->andReturn(new SnmpResponse("sysDescr.0 = Linux 6.0\n"));

        $query = (new SnmpQuery($mockBackend))
            ->device($this->device)
            ->numeric();

        $response = $query->get('sysDescr.0');

        $this->assertSame("sysDescr.0 = Linux 6.0\n", $response->raw);
    }

    public function testWalkDelegatesForEachOid(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (SnmpConfig $config, string $oid) => $oid === 'ifDescr')
            ->andReturn(new SnmpResponse("ifDescr.1 = eth0\n"));

        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (SnmpConfig $config, string $oid) => $oid === 'ifType')
            ->andReturn(new SnmpResponse("ifType.1 = ethernetCsmacd\n"));

        $query = (new SnmpQuery($mockBackend))->device($this->device);
        $response = $query->walk(['ifDescr', 'ifType']);

        $this->assertSame("ifDescr.1 = eth0\nifType.1 = ethernetCsmacd\n", $response->raw);
    }

    public function testNextDelegatesToBackend(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('next')
            ->once()
            ->withArgs(fn (SnmpConfig $config, array $oids) => $oids === ['sysDescr.0'])
            ->andReturn(new SnmpResponse("sysObjectID.0 = .1.3.6.1.4.1\n"));

        $query = (new SnmpQuery($mockBackend))->device($this->device);
        $response = $query->next('sysDescr.0');

        $this->assertSame("sysObjectID.0 = .1.3.6.1.4.1\n", $response->raw);
    }

    public function testLimitOidsChunksRequests(): void
    {
        $device = new Device([
            'hostname' => '10.1.2.3',
            'snmpver' => 'v2c',
            'community' => 'test-community',
        ]);
        $device->device_id = 1;
        // set max_oid to 2
        $attrib = new \App\Models\DeviceAttrib(['device_id' => 1, 'attrib_type' => 'snmp_max_oid', 'attrib_value' => '2']);
        $device->setRelation('attribs', new \Illuminate\Database\Eloquent\Collection([$attrib]));

        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (SnmpConfig $config, array $oids) => $oids === ['oid1', 'oid2'])
            ->andReturn(new SnmpResponse("oid1 = 1\noid2 = 2\n"));

        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (SnmpConfig $config, array $oids) => $oids === ['oid3'])
            ->andReturn(new SnmpResponse("oid3 = 3\n"));

        $query = (new SnmpQuery($mockBackend))->device($device);
        $response = $query->get(['oid1', 'oid2', 'oid3']);

        $this->assertSame("oid1 = 1\noid2 = 2\noid3 = 3\n", $response->raw);
    }

    public function testAbortOnFailure(): void
    {
        $mockBackend = $this->mockBackend();
        // First OID fails (exitCode 1)
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (SnmpConfig $config, string $oid) => $oid === 'unsupportedOid')
            ->andReturn(new SnmpResponse('', 'Timeout: No Response', 1));

        // Second OID should never be queried because of abortOnFailure
        $mockBackend->shouldNotReceive('walk')
            ->withArgs(fn (SnmpConfig $config, string $oid) => $oid === 'secondOid');

        $query = (new SnmpQuery($mockBackend))
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
            ->withArgs(fn (SnmpConfig $config, array $oids) => $oids === ['sysDescr.0'])
            ->andReturn(new SnmpResponse("sysDescr.0 = CachedLinux\n"));

        $query = (new SnmpQuery($mockBackend))
            ->device($this->device)
            ->cache();

        $first = $query->get('sysDescr.0');
        $second = $query->get('sysDescr.0');

        $this->assertSame("sysDescr.0 = CachedLinux\n", $first->raw);
        $this->assertSame("sysDescr.0 = CachedLinux\n", $second->raw);
    }

    public function testTranslateDelegatesToTranslateBackend(): void
    {
        $mockBackend = $this->mockBackend();
        $mockTranslate = Mockery::mock(SnmpTranslatorInterface::class);
        $mockTranslate->shouldReceive('translate')
            ->once()
            ->withArgs(fn (string $oid, SnmpQueryOptions $options) => $oid === 'IF-MIB::ifTable')
            ->andReturn('.1.3.6.1.2.1.2.2');

        $query = (new SnmpQuery($mockBackend, $mockTranslate))
            ->device($this->device)
            ->numeric();

        $result = $query->translate('IF-MIB::ifTable');
        $this->assertSame('.1.3.6.1.2.1.2.2', $result);
    }

    public function testContextV3Prefix(): void
    {
        $v3Device = new Device([
            'hostname' => '10.1.2.3',
            'snmpver' => 'v3',
            'authlevel' => 'noAuthNoPriv',
        ]);
        $v3Device->device_id = 2;

        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (SnmpConfig $config, array $oids, SnmpQueryOptions $options) => $options->context === 'vlan-100')
            ->andReturn(new SnmpResponse("val = 1\n"));

        $query = (new SnmpQuery($mockBackend))
            ->device($v3Device)
            ->context('100', 'vlan-');

        $query->get('val');
    }

    public function testOptionsConvertsNetSnmpFlagsToQueryOptions(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (SnmpConfig $config, string $oid, SnmpQueryOptions $options) => $options->numericIndexes === true
                && $options->oidFormat === SnmpOidOutput::Suffix
                && $options->numericEnums === false
                && $options->tolerateUnorderedIndexes === true)
            ->andReturn(new SnmpResponse("test = 1\n"));

        $query = (new SnmpQuery($mockBackend))
            ->device($this->device)
            ->options(['-OQUsb', '-Cc']);

        $response = $query->walk('test');
        $this->assertSame("test = 1\n", $response->raw);
    }

    public function testSnmpQueryDispatchesSnmpQueryExecutedEvent(): void
    {
        \Illuminate\Support\Facades\Event::fake([\App\Events\SnmpQueryExecuted::class]);

        $mockBackend = Mockery::mock(SnmpBackendInterface::class);
        $mockBackend->shouldReceive('get')
            ->once()
            ->andReturn(new SnmpResponse("sysDescr.0 = Linux 6.0\n", command: ['/usr/bin/snmpget', 'sysDescr.0']));

        $query = (new SnmpQuery($mockBackend))->device($this->device);
        $query->get('sysDescr.0');

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\SnmpQueryExecuted::class, fn (\App\Events\SnmpQueryExecuted $event) => $event->method === 'snmpget'
            && $event->oids === ['sysDescr.0']
            && $event->cliCommand === ['/usr/bin/snmpget', 'sysDescr.0']
            && $event->device === $this->device
            && $event->response->raw === "sysDescr.0 = Linux 6.0\n");
    }

    public function testSnmpQueryDefaultsToQuickPrintOptions(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (SnmpConfig $config, array $oids, SnmpQueryOptions $options) => $options->quickPrint === true
                    && $options->extendedIndex === true
                    && $options->printUnits === false
                    && $options->numericEnums === true
                    && $options->numericTimeticks === true)
            ->andReturn(new SnmpResponse("sysDescr.0 = Linux\n"));

        $query = (new SnmpQuery($mockBackend))->device($this->device);
        $query->get('sysDescr.0');
    }

    public function testHideMibSetsSuffixOidFormat(): void
    {
        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('get')
            ->once()
            ->withArgs(fn (SnmpConfig $config, array $oids, SnmpQueryOptions $options) => $options->oidFormat === SnmpOidOutput::Suffix)
            ->andReturn(new SnmpResponse("sysDescr.0 = Linux\n"));

        $query = (new SnmpQuery($mockBackend))->device($this->device)->hideMib();
        $query->get('sysDescr.0');
    }

    public function testPrepareOptionsDisablesBulkForNoBulkOids(): void
    {
        $this->device->os = 'generic';
        \App\Facades\LibrenmsConfig::set('os.generic.oids.no_bulk', ['UCD-SNMP-MIB::laLoadInt']);

        $mockBackend = $this->mockBackend();
        $mockBackend->shouldReceive('walk')
            ->once()
            ->withArgs(fn (SnmpConfig $config, string $oid, SnmpQueryOptions $options) => $options->allowBulk === false)
            ->andReturn(new SnmpResponse("laLoadInt = 1\n"));

        $query = (new SnmpQuery($mockBackend))->device($this->device);
        $query->walk('UCD-SNMP-MIB::laLoadInt');
    }
}
