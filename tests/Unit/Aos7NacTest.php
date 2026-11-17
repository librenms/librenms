<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\PortCache;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

class Aos7NacTest extends TestCase
{
    #[DataProvider('nacCases')]
    public function testNacAddressAndAuthorization(string $rawIp, string $hexIp, string $message, int $status, int $classification, string $expectedIp, string $expectedAuthz): void
    {
        $prefix = 'ALCATEL-IND1-DA-MIB::';
        $index = '1001.00:11:22:33:44:55.10';
        $table = [];
        foreach ([
            'alaDaMacVlanUserIpAddress' => $rawIp,
            'alaDaMacVlanUserAuthStatus' => (string) $status,
            'alaDaMacVlanUserClassificationSource' => (string) $classification,
            'alaDaMacVlanUserServerMessage' => $message,
        ] as $column => $value) {
            $table["$prefix$column.$index"] = $value;
        }

        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('mibDir')->with('nokia/aos7')->andReturnSelf();
        $query->shouldReceive('options')->with(Mockery::on(fn ($options) => $options instanceof SnmpQueryOptions && $options->stringFormat === SnmpStringOutput::Hex))->andReturnSelf();
        $query->shouldReceive('walk')->with("{$prefix}alaDaMacVlanUserTable")->andReturn(new SnmpResponse($table));
        $query->shouldReceive('walk')->with("{$prefix}alaDaMacVlanUserIpAddress")->andReturn(new SnmpResponse(["{$prefix}alaDaMacVlanUserIpAddress.$index" => $hexIp]));
        $query->shouldReceive('walk')->with("{$prefix}alaDaUNPPort8021XSuppTimeOut")->andReturn(new SnmpResponse);
        $this->app->instance(SnmpQueryBuilder::class, $query);
        PortCache::shouldReceive('getIdFromIfIndex')->andReturn(71);

        $device = ['os' => 'aos7', 'device_id' => 1];
        $os = OS::make($device);
        $this->assertInstanceOf(\LibreNMS\Interfaces\Polling\NacPolling::class, $os);
        $entries = $os->pollNac();
        $this->assertCount(1, $entries);
        $this->assertSame($expectedIp, $entries->first()->ip_address);
        $this->assertSame($expectedAuthz, $entries->first()->authz_status);
    }

    /**
     * @return array<string, array{string, string, string, int, int, string, string}>
     */
    public static function nacCases(): array
    {
        return [
            'printable octets and informational message' => ['d@AB', '64 40 41 42', 'Welcome to Block A', 3, 0, '100.64.65.66', 'authzSuccess'],
            'decimal-looking octets' => ['1234', '31 32 33 34', '', 3, 0, '49.50.51.52', 'authzSuccess'],
            'informational message with valid address' => ['192.168.1.1', 'C0 A8 01 01', 'Welcome to Block A', 3, 0, '192.168.1.1', 'authzSuccess'],
            'hex octets and failed authentication' => ['C0 A8 01 01', 'C0 A8 01 01', 'Rejected', 4, 0, '192.168.1.1', 'authzFail'],
            'legacy decimal fallback' => ['3232235777', '', '', 3, 0, '192.168.1.1', 'authzSuccess'],
            'legacy dotted fallback' => ['192.168.1.1', '', '', 3, 0, '192.168.1.1', 'authzSuccess'],
            'legacy hex fallback' => ['C0 A8 01 01', '', '', 3, 0, '192.168.1.1', 'authzSuccess'],
            'blocked UNP' => ['192.168.1.1', 'C0 A8 01 01', '', 1, 60, '192.168.1.1', 'authzFail'],
        ];
    }

    public function testAddressWalkTimeoutStopsNacPolling(): void
    {
        $prefix = 'ALCATEL-IND1-DA-MIB::';
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('mibDir')->with('nokia/aos7')->andReturnSelf();
        $query->shouldReceive('options')->andReturnSelf();
        $query->shouldReceive('walk')->with("{$prefix}alaDaMacVlanUserTable")
            ->andReturn(new SnmpResponse(["{$prefix}alaDaMacVlanUserAuthStatus.1001.00:11:22:33:44:55.10" => '3']));
        $query->shouldReceive('walk')->with("{$prefix}alaDaMacVlanUserIpAddress")
            ->andReturn(new SnmpResponse([], 'Timeout: No Response from udp:192.0.2.1:161', 1));
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $device = ['os' => 'aos7', 'device_id' => 1];
        $os = OS::make($device);
        $this->assertInstanceOf(\LibreNMS\Interfaces\Polling\NacPolling::class, $os);
        $this->expectException(\LibreNMS\Exceptions\SnmpException::class);
        $os->pollNac();
    }
}
