<?php

namespace LibreNMS\Tests\Unit\OS;

use LibreNMS\OS\Openwrt;
use LibreNMS\Tests\TestCase;

final class OpenwrtTest extends TestCase
{
    public function testParseInterfaceLineSupportsCurrentCommaSeparatedFormat(): void
    {
        $this->assertSame(
            ['wlan0', 'radio0 (MySSID)'],
            $this->parseInterfaceLine('wlan0,radio0 (MySSID)')
        );
    }

    public function testParseInterfaceLineSupportsLegacyWhitespaceSeparatedFormat(): void
    {
        $this->assertSame(
            ['wlan0', 'wl-2.4G'],
            $this->parseInterfaceLine('wlan0 wl-2.4G')
        );
    }

    public function testParseInterfaceLineFallsBackToSingleTokenMapping(): void
    {
        $this->assertSame(
            ['wlan0', 'wlan0'],
            $this->parseInterfaceLine('wlan0')
        );
    }

    private function parseInterfaceLine(string $line): ?array
    {
        $reflectionClass = new \ReflectionClass(Openwrt::class);
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $method = $reflectionClass->getMethod('parseInterfaceLine');

        return $method->invoke($instance, $line);
    }
}
