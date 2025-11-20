<?php

namespace LibreNMS\Tests\Unit\Util;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Socket;
use Mockery;
use Net_DNS2_Exception;
use Net_DNS2_Lookups;
use Net_DNS2_Resolver;

class DnsTest extends TestCase
{
    private $resolverMock;
    private $socketMock;
    private $dns;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolverMock = Mockery::mock(Net_DNS2_Resolver::class);
        $this->socketMock = Mockery::mock(Socket::class);
        $this->dns = new Dns($this->resolverMock, $this->socketMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLookupIpReturnsOverwriteIpWhenSet(): void
    {
        $dev = $this->device(overwriteIp: '192.168.1.100');

        $this->assertSame('192.168.1.100', $this->dns->lookupIp($dev));
    }

    public function testLookupIpReturnsHostnameWhenItIsValidIp(): void
    {
        $dev = $this->device(hostname: '192.168.1.1');

        $this->assertSame('192.168.1.1', $this->dns->lookupIp($dev));
    }

    public function testLookupIpReturnsIpv6AddressWhenHostnameIsValidIpv6(): void
    {
        $device = new Device();
        $device->hostname = '2001:db8::1';

        $result = $this->dns->lookupIp($device);

        $this->assertEquals('2001:db8::1', $result);
    }

    public function testLookupIpResolvesHostnameWithDefaultMode(): void
    {
        $this->mockConfig('os');
        $dev = $this->device(transport: 'udp');

        $this->expectAddrInfo('example.com', null, $this->addr4('192.168.1.1'));

        $this->assertSame('192.168.1.1', $this->dns->lookupIp($dev));
    }

    public function testLookupIpWithPreferIpv6Mode(): void
    {
        $this->mockConfig('prefer_ipv6');
        $dev = $this->device();

        $this->expectAddrInfo('example.com', AddressFamily::IPv6, $this->addr6('2001:db8::1'));

        $this->assertSame('2001:db8::1', $this->dns->lookupIp($dev));
    }

    public function testLookupIpWithPreferIpv6ModeFallsBackToIpv4(): void
    {
        $this->mockConfig('prefer_ipv6');
        $dev = $this->device();

        // IPv6 lookup returns unusable
        $this->expectAddrInfo('example.com', AddressFamily::IPv6, []);

        // fallback → IPv4
        $this->expectAddrInfo('example.com', null, $this->addr4('192.168.1.1'));

        $this->assertSame('192.168.1.1', $this->dns->lookupIp($dev));
    }

    public function testLookupIpWithIpv6OnlyMode(): void
    {
        $this->mockConfig('ipv6_only');
        $device = $this->device();

        $this->expectAddrInfo('example.com', AddressFamily::IPv6, $this->addr6('2001:db8::1'));

        $result = $this->dns->lookupIp($device);

        $this->assertEquals('2001:db8::1', $result);
    }

    public function testLookupIpWithPreferIpv4Mode(): void
    {
        $this->mockConfig('prefer_ipv4');
        $device = $this->device();

        $this->expectAddrInfo('example.com', AddressFamily::IPv4, $this->addr4('192.168.1.1'));

        $result = $this->dns->lookupIp($device);

        $this->assertEquals('192.168.1.1', $result);
    }

    public function testLookupIpWithPreferIpv4ModeFallsBackToDefault(): void
    {
        $this->mockConfig('prefer_ipv4');
        $device = $this->device();

        // First call to IPv4 returns empty array (no IPv4 address found)
        $this->expectAddrInfo('example.com', AddressFamily::IPv4, []);

        // Second call falls back to default
        $this->expectAddrInfo('example.com', null, $this->addr6('2001:db8::1'));

        $result = $this->dns->lookupIp($device);

        $this->assertEquals('2001:db8::1', $result);
    }

    public function testLookupIpWithIpv4OnlyMode(): void
    {
        $this->mockConfig('ipv4_only');
        $device = $this->device();

        $this->expectAddrInfo('example.com', AddressFamily::IPv4, $this->addr4('192.168.1.1'));

        $result = $this->dns->lookupIp($device);

        $this->assertEquals('192.168.1.1', $result);
    }

    public function testLookupIpReturnsCachedIpWhenResolutionFails(): void
    {
        $this->mockConfig('ipv4_only');
        $device = $this->device(hostname: 'example.com', ip: '192.168.1.50');

        $this->socketMock->shouldReceive('getAddrInfo')
            ->with('example.com', AddressFamily::IPv4)
            ->once()
            ->andReturn(false);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $dns->shouldReceive('lookupFailedShouldClearIpCache')
            ->with($device)
            ->once()
            ->andReturn(false);

        $result = $dns->lookupIp($device);

        $this->assertEquals('192.168.1.50', $result);
    }

    public function testLookupIpReturnsNullWhenResolutionFailsAndCacheShouldBeCleared(): void
    {
        $this->mockConfig('ipv4_only');
        $device = $this->device(hostname: 'example.com', ip: '192.168.1.50');

        $this->socketMock->shouldReceive('getAddrInfo')
            ->with('example.com', AddressFamily::IPv4)
            ->once()
            ->andReturn(false);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $dns->shouldReceive('lookupFailedShouldClearIpCache')
            ->with($device)
            ->once()
            ->andReturn(true);

        $result = $dns->lookupIp($device);

        $this->assertNull($result);
    }

    public function testGetRecordReturnsAnswerOnSuccess(): void
    {
        $mockResponse = $this->dnsResponse([(object) ['type' => 'A', 'address' => '192.168.1.1']]);

        $this->resolverMock->shouldReceive('query')
            ->with('example.com', 'A')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->dns->getRecord('example.com', 'A');

        $this->assertCount(1, $result);
        $this->assertEquals('192.168.1.1', $result[0]->address);
    }

    public function testGetRecordReturnsEmptyArrayOnException(): void
    {
        $this->resolverMock->shouldReceive('query')
            ->with('invalid.domain', 'A')
            ->once()
            ->andThrow(new Net_DNS2_Exception('Query failed'));

        $result = $this->dns->getRecord('invalid.domain', 'A');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetRecordUsesARecordByDefault(): void
    {
        $mockResponse = $this->dnsResponse();

        $this->resolverMock->shouldReceive('query')
            ->with('example.com', 'A')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->dns->getRecord('example.com');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetCoordinatesReturnsLatLngFromLocRecord(): void
    {
        $mockResponse = $this->dnsResponse([(object) ['latitude' => 40.7128, 'longitude' => -74.0060]]);

        $this->resolverMock->shouldReceive('query')
            ->with('example.com', 'LOC')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->dns->getCoordinates('example.com');

        $this->assertEquals(['lat' => 40.7128, 'lng' => -74.0060], $result);
    }

    public function testGetCoordinatesReturnsEmptyArrayWhenNoRecordsFound(): void
    {
        $mockResponse = $this->dnsResponse();

        $this->resolverMock->shouldReceive('query')
            ->with('example.com', 'LOC')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->dns->getCoordinates('example.com');

        $this->assertEquals([], $result);
    }

    public function testGetCoordinatesReturnsNullValuesWhenRecordMissingCoordinates(): void
    {
        $mockResponse = $this->dnsResponse([(object) []]);

        $this->resolverMock->shouldReceive('query')
            ->with('example.com', 'LOC')
            ->once()
            ->andReturn($mockResponse);

        $result = $this->dns->getCoordinates('example.com');

        $this->assertEquals(['lat' => null, 'lng' => null], $result);
    }

    public function testResolveIpReturnsIpv4Address(): void
    {
        $this->expectAddrInfo('example.com', null, $this->addr4('192.168.1.1'));

        $result = $this->dns->resolveIP('example.com');

        $this->assertEquals('192.168.1.1', $result);
    }

    public function testResolveIpReturnsIpv6Address(): void
    {
        $this->expectAddrInfo('example.com', AddressFamily::IPv6, $this->addr6('2001:db8::1'));

        $result = $this->dns->resolveIP('example.com', AddressFamily::IPv6);

        $this->assertEquals('2001:db8::1', $result);
    }

    public function testResolveIpReturnsFalseOnFailure(): void
    {
        $this->socketMock->shouldReceive('getAddrInfo')
            ->with('invalid.domain', null)
            ->once()
            ->andReturn(false);

        $result = $this->dns->resolveIP('invalid.domain');

        $this->assertFalse($result);
    }

    public function testResolveIpReturnsNullWhenAddressNotInResponse(): void
    {
        $this->expectAddrInfo('example.com', null, [
            [
                'ai_flags' => 0,
                'ai_family' => AF_INET,
                'ai_socktype' => SOCK_STREAM,
                'ai_protocol' => 6,
                'ai_addr' => [
                    'sin_port' => 0,
                ],
            ],
        ]);

        $result = $this->dns->resolveIP('example.com');

        $this->assertNull($result);
    }

    public function testLookupFailedShouldClearIpCacheReturnsFalseWhenIpIsNull(): void
    {
        $device = new Device();
        $device->ip = null;

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $result = $dns->lookupFailedShouldClearIpCache($device);

        $this->assertFalse($result);
    }

    public function testLookupFailedShouldClearIpCacheReturnsTrueOnNxDomain(): void
    {
        $this->mockConfig('ipv4_only');
        $device = $this->device(ip: '192.168.1.1');

        $exception = new Net_DNS2_Exception('NXDOMAIN', Net_DNS2_Lookups::RCODE_NXDOMAIN);

        $this->resolverMock->shouldReceive('query')
            ->with('192.168.1.1', 'A')
            ->once()
            ->andThrow($exception);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $result = $dns->lookupFailedShouldClearIpCache($device);

        $this->assertTrue($result);
    }

    public function testLookupFailedShouldClearIpCacheReturnsFalseOnOtherException(): void
    {
        $this->mockConfig('ipv4_only');
        $device = $this->device(ip: '192.168.1.1');

        $exception = new Net_DNS2_Exception('Temporary failure', Net_DNS2_Lookups::RCODE_SERVFAIL);

        $this->resolverMock->shouldReceive('query')
            ->with('192.168.1.1', 'A')
            ->once()
            ->andThrow($exception);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $result = $dns->lookupFailedShouldClearIpCache($device);

        $this->assertFalse($result);
    }

    public function testLookupFailedShouldClearIpCacheReturnsFalseOnSuccessfulQuery(): void
    {
        $this->mockConfig('ipv4_only');
        $device = $this->device(ip: '192.168.1.1');
        $mockResponse = $this->dnsResponse();

        $this->resolverMock->shouldReceive('query')
            ->with('192.168.1.1', 'A')
            ->once()
            ->andReturn($mockResponse);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $result = $dns->lookupFailedShouldClearIpCache($device);

        $this->assertFalse($result);
    }

    public function testLookupFailedShouldClearIpCacheUsesCorrectRecordTypesForPreferIpv4(): void
    {
        $this->mockConfig('prefer_ipv4');
        $device = $this->device(ip: '192.168.1.1');
        $mockResponse = $this->dnsResponse();

        $this->resolverMock->shouldReceive('query')
            ->with('192.168.1.1', 'A')
            ->once()
            ->andReturn($mockResponse);

        $this->resolverMock->shouldReceive('query')
            ->with('192.168.1.1', 'AAAA')
            ->once()
            ->andReturn($mockResponse);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $result = $dns->lookupFailedShouldClearIpCache($device);

        $this->assertFalse($result);
    }

    public function testLookupFailedShouldClearIpCacheUsesCorrectRecordTypesForIpv6Only(): void
    {
        $this->mockConfig('ipv6_only');
        $device = $this->device(ip: '2001:db8::1');
        $mockResponse = $this->dnsResponse();

        $this->resolverMock->shouldReceive('query')
            ->with('2001:db8::1', 'AAAA')
            ->once()
            ->andReturn($mockResponse);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $result = $dns->lookupFailedShouldClearIpCache($device);

        $this->assertFalse($result);
    }

    public function testLookupFailedShouldClearIpCacheUsesCorrectRecordTypesForOsMode(): void
    {
        $this->mockConfig('os');
        $device = $this->device(ip: '192.168.1.1', transport: 'udp');
        $mockResponse = $this->dnsResponse();

        $this->resolverMock->shouldReceive('query')
            ->with('192.168.1.1', 'A')
            ->once()
            ->andReturn($mockResponse);

        $this->resolverMock->shouldReceive('query')
            ->with('192.168.1.1', 'AAAA')
            ->once()
            ->andReturn($mockResponse);

        $dns = Mockery::mock(Dns::class, [$this->resolverMock, $this->socketMock])
            ->makePartial();

        $result = $dns->lookupFailedShouldClearIpCache($device);

        $this->assertFalse($result);
    }

    // helper functions

    private function device(
        string $hostname = 'example.com',
        ?string $overwriteIp = null,
        ?string $ip = null,
        ?string $transport = null,
    ): Device {
        $d = new Device();
        $d->hostname = $hostname;
        $d->overwrite_ip = $overwriteIp;
        $d->ip = $ip;
        $d->transport = $transport;

        return $d;
    }

    private function mockConfig(string $mode): void
    {
        LibrenmsConfig::shouldReceive('get')
            ->with('dns.resolution_mode')
            ->andReturn($mode);
    }

    private function expectAddrInfo(
        string $hostname,
        ?AddressFamily $af,
        array $result,
        int $times = 1
    ): void {
        $this->socketMock->shouldReceive('getAddrInfo')
            ->with($hostname, $af)
            ->times($times)
            ->andReturn($result);
    }

    private function addr4(string $ip): array
    {
        return [
            [
                'ai_family' => AF_INET,
                'ai_addr' => ['sin_addr' => $ip, 'sin_port' => 0],
            ],
        ];
    }

    private function addr6(string $ip): array
    {
        return [
            [
                'ai_family' => AF_INET6,
                'ai_addr' => ['sin6_addr' => $ip, 'sin6_port' => 0],
            ],
        ];
    }

    protected function dnsResponse(array $answer = [], array $authority = [], array $additional = []): object
    {
        return new class($answer, $authority, $additional)
        {
            public function __construct(
                public array $answer,
                public array $authority,
                public array $additional,
            ) {
            }
        };
    }
}
