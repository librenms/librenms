<?php

namespace LibreNMS\Tests\Unit;

use App\Facades\PortCache;
use Illuminate\Support\Facades\Date;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Enum\SnmpStringOutput;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type NacClient array{index?: string, unpUsed?: string, unpFromAuthServer?: string, loginTimestamp?: string, expectedLoginTime?: string}
 */
class Aos7NacTest extends TestCase
{
    /**
     * @param  NacClient  $client
     */
    #[DataProvider('nacCases')]
    public function testNacClient(string $rawIp, string $hexIp, string $message, int $status, int $classification, string $expectedIp, string $expectedAuthz, int $authType = 0, array $client = []): void
    {
        $now = Date::parse('2026-10-13 15:00:00 UTC');
        $this->travelTo($now);

        $prefix = 'ALCATEL-IND1-DA-MIB::';
        $index = $client['index'] ?? '1001.00:11:22:33:44:55.10';
        $table = [];
        foreach ([
            'alaDaMacVlanUserIpAddress' => $rawIp,
            'alaDaMacVlanUserAuthStatus' => (string) $status,
            'alaDaMacVlanUserAuthtype' => (string) $authType,
            'alaDaMacVlanUserClassificationSource' => (string) $classification,
            'alaDaMacVlanUserServerMessage' => $message,
            'alaDaMacVlanUserUnpUsed' => $client['unpUsed'] ?? '',
            'alaDaMacVlanUserUnpFromAuthServer' => $client['unpFromAuthServer'] ?? '',
            'alaDaMacVlanUserLoginTimeStamp' => $client['loginTimestamp'] ?? '',
        ] as $column => $value) {
            $table["$prefix$column.$index"] = $value;
        }

        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('options')->with(Mockery::on(fn ($options) => $options instanceof SnmpQueryOptions && $options->stringFormat === SnmpStringOutput::Hex))->andReturnSelf();
        $query->shouldReceive('walk')->with("{$prefix}alaDaMacVlanUserTable")->andReturn(new SnmpResponse($table));
        $query->shouldReceive('walk')->with("{$prefix}alaDaMacVlanUserIpAddress")->andReturn(new SnmpResponse(["{$prefix}alaDaMacVlanUserIpAddress.$index" => $hexIp]));
        $timeoutOid = "{$prefix}alaDaUNPPort8021XSuppTimeOut";
        $timeoutWalk = $query->shouldReceive('walk')->with($timeoutOid);
        if ($authType === 2) {
            $timeoutWalk->once()->andReturn(new SnmpResponse(["$timeoutOid.1001" => '60']));
        } else {
            $timeoutWalk->never();
        }
        $this->app->instance(SnmpQueryBuilder::class, $query);
        PortCache::shouldReceive('getIdFromIfIndex')->once()->with(1001, 1)->andReturn(71);

        $device = ['os' => 'aos7', 'device_id' => 1];
        $os = OS::make($device);
        $this->assertInstanceOf(\LibreNMS\Interfaces\Polling\NacPolling::class, $os);
        $entries = $os->pollNac();
        $this->assertCount(1, $entries);
        $entry = $entries->first();
        $this->assertSame('1001-001122334455-10', $entry->auth_id);
        $this->assertSame('001122334455', $entry->mac_address);
        $this->assertSame('00:11:22:33:44:55', $entry->username);
        $this->assertSame('singleHost', $entry->host_mode);
        $this->assertSame($expectedIp, $entry->ip_address);
        $this->assertSame($expectedAuthz, $entry->authz_status);
        $this->assertEquals($authType === 2 ? 60 : 0, $entry->timeout);
        $this->assertSame(
            isset($client['expectedLoginTime']) ? $now->getTimestamp() - Date::parse($client['expectedLoginTime'])->getTimestamp() : null,
            $entry->time_elapsed
        );
    }

    /**
     * @return array<string, array{string, string, string, int, int, string, string, 7?: int, 8?: NacClient}>
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
            'quoted hex with a prefix and dashes' => ['', '"0xC0-A8-01-01"', '', 3, 0, '192.168.1.1', 'authzSuccess'],
            'invalid hex falls back to the raw address' => ['192.168.1.1', 'C0A801G1', '', 3, 0, '192.168.1.1', 'authzSuccess'],
            'blocked UNP' => ['192.168.1.1', 'C0 A8 01 01', '', 1, 60, '192.168.1.1', 'authzFail'],
            'authenticated client with wrong UNP' => ['192.168.1.1', '', '', 3, 0, '192.168.1.1', 'authzFail', 0, [
                'unpUsed' => 'fallback',
                'unpFromAuthServer' => 'staff',
            ]],
            'UNP comparison ignores case' => ['192.168.1.1', '', '', 3, 0, '192.168.1.1', 'authzSuccess', 0, [
                'unpUsed' => 'Staff',
                'unpFromAuthServer' => 'staff',
            ]],
            'decimal MAC index' => ['192.168.1.1', '', '', 3, 0, '192.168.1.1', 'authzSuccess', 0, [
                'index' => '1001.0.17.34.51.68.85.10',
            ]],
            '802.1X client' => ['192.168.1.1', 'C0 A8 01 01', '', 3, 0, '192.168.1.1', 'authzSuccess', 2],
            'login timestamp with a UTC offset' => ['', '', '', 3, 0, '0.0.0.0', 'authzSuccess', 0, [
                'loginTimestamp' => '2026-10-13,9:0:35.0,+2:0',
                'expectedLoginTime' => '2026-10-13 07:00:35 UTC',
            ]],
            'login timestamp with a negative offset under an hour' => ['', '', '', 3, 0, '0.0.0.0', 'authzSuccess', 0, [
                'loginTimestamp' => '2026-10-13,9:0:35.0,-0:30',
                'expectedLoginTime' => '2026-10-13 09:30:35 UTC',
            ]],
            'login timestamp with no UTC offset' => ['', '', '', 3, 0, '0.0.0.0', 'authzSuccess', 0, [
                'loginTimestamp' => '2026-10-13,0:0:35.0',
                'expectedLoginTime' => '2026-10-13 00:00:35',
            ]],
            'relative date from the device is ignored' => ['', '', '', 3, 0, '0.0.0.0', 'authzSuccess', 0, [
                'loginTimestamp' => 'yesterday',
            ]],
            'invalid login time is ignored' => ['', '', '', 3, 0, '0.0.0.0', 'authzSuccess', 0, [
                'loginTimestamp' => '2026-10-13,99:0:35.0',
            ]],
        ];
    }

    public function testAddressWalkTimeoutStopsNacPolling(): void
    {
        $prefix = 'ALCATEL-IND1-DA-MIB::';
        $query = Mockery::mock(SnmpQueryBuilder::class);
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
