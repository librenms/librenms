<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\SearchTerm;
use PHPUnit\Framework\Attributes\DataProvider;

final class SearchTermTest extends TestCase
{
    public function testExactIpv4(): void
    {
        $term = SearchTerm::parse(' 192.168.1.10 ');

        $this->assertSame('192.168.1.10', $term->value);
        $this->assertInstanceOf(IPv4::class, $term->ip);
        $this->assertTrue($term->isExactIp());
        $this->assertTrue($term->isIpv4());
        $this->assertFalse($term->isIpv6());
        $this->assertFalse($term->isMac()); // 192168110 is hex, but an IP
        $this->assertNull($term->networkRange());
    }

    public function testIpv4Network(): void
    {
        $term = SearchTerm::parse('10.1.2.3/23');

        $this->assertTrue($term->isNetwork);
        $this->assertFalse($term->isExactIp());
        $this->assertSame(['0A010200', '0A0103FF'], $term->networkRange());
    }

    public function testIpv6(): void
    {
        $term = SearchTerm::parse('2001:db8::1');
        $this->assertInstanceOf(IPv6::class, $term->ip);
        $this->assertTrue($term->isExactIp());
        $this->assertTrue($term->isIpv6());
        $this->assertFalse($term->isMac());

        $network = SearchTerm::parse('2001:db8::/32');
        $this->assertSame(['20010DB8000000000000000000000000', '20010DB8FFFFFFFFFFFFFFFFFFFFFFFF'], $network->networkRange());
    }

    #[DataProvider('fragments')]
    public function testFragments(string $search, bool $ipv4, bool $ipv6, ?string $macHex): void
    {
        $term = SearchTerm::parse($search);

        $this->assertNull($term->ip);
        $this->assertSame($ipv4, $term->isIpv4(), 'ipv4');
        $this->assertSame($ipv6, $term->isIpv6(), 'ipv6');
        $this->assertSame($macHex, $term->macHex, 'mac');
    }

    public static function fragments(): array
    {
        return [
            'ipv4 prefix' => ['10.1.', true, false, null],
            'ipv4 suffix' => ['.10', true, false, null],
            'ipv6 start' => ['2001:db8:', false, true, '2001db8'],
            'partial mac colons' => ['74:29:72', false, true, '742972'],
            'partial mac cisco' => ['0011.2233', false, false, '00112233'],
            'short hex word' => ['cafe', false, false, null],
            'six hex digits' => ['abcdef', false, false, 'abcdef'],
            'text' => ['core-sw1', false, false, null],
        ];
    }

    #[DataProvider('fullMacs')]
    public function testFullMac(string $search): void
    {
        $term = SearchTerm::parse($search);

        $this->assertTrue($term->isFullMac());
        $this->assertSame('001122aabbcc', $term->macHex);
        $this->assertFalse($term->isIp());
    }

    public static function fullMacs(): array
    {
        return [
            ['00:11:22:AA:BB:CC'],
            ['00-11-22-aa-bb-cc'],
            ['0011.22aa.bbcc'],
            ['001122aabbcc'],
            ['0:11:22:aa:bb:cc'],
        ];
    }

    public function testHostname(): void
    {
        $this->assertTrue(SearchTerm::parse('printer01.example.com')->isHostname());
        $this->assertTrue(SearchTerm::parse('printer01.example.com.')->isHostname());
        $this->assertFalse(SearchTerm::parse('printer01')->isHostname());
        $this->assertFalse(SearchTerm::parse('10.0.0.1')->isHostname());
        $this->assertFalse(SearchTerm::parse('not a host.name')->isHostname());
    }
}
