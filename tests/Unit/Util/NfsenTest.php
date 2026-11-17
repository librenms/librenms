<?php

namespace LibreNMS\Tests\Unit\Util;

use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Nfsen;
use PHPUnit\Framework\Attributes\DataProvider;

final class NfsenTest extends TestCase
{
    public function testHostname(): void
    {
        $this->assertSame('router.example.com', Nfsen::hostname('router.example.com'));

        LibrenmsConfig::set('nfsen_split_char', '_');
        $this->assertSame('router_example_com', Nfsen::hostname('router.example.com'));

        LibrenmsConfig::set('nfsen_suffix', '_example_com');
        $this->assertSame('router', Nfsen::hostname('router.example.com'));
    }

    #[DataProvider('layouts')]
    public function testSubPath(int $layout, string $expected): void
    {
        LibrenmsConfig::set('nfsen_subdirlayout', $layout);
        $time = mktime(13, 47, 12, 3, 9, 2025); // rounds down to 13:45

        $this->assertSame($expected, Nfsen::subPath($time));
    }

    /**
     * @return array<array{int, string}>
     */
    public static function layouts(): array
    {
        return [
            [0, 'nfcapd.202503091345'],
            [1, '2025/03/09/nfcapd.202503091345'],
            [2, '2025/03/09/13/nfcapd.202503091345'],
            [3, '2025/10/0/nfcapd.202503091345'],
            [5, '2025/67/nfcapd.202503091345'],
            [7, '2025-03-09/nfcapd.202503091345'],
            [8, '2025-03-09/13/nfcapd.202503091345'],
        ];
    }
}
