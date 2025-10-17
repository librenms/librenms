<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Oid;

final class OidTest extends TestCase
{
    public function testStringFromOidSingle(): void
    {
        // 3 characters: 'A' (65) 'B' (66) 'C' (67)
        $oid = '3.65.66.67';
        $this->assertSame('ABC', Oid::stringFromOid($oid));
    }

    public function testStringFromOidMultiplePositions(): void
    {
        // two strings: 'ABC' and 'xy'
        $oid = '3.65.66.67.2.120.121';
        $this->assertSame('ABC', Oid::stringFromOid($oid, 0));
        $this->assertSame('xy', Oid::stringFromOid($oid, 1));
    }

    public function testStringFromOidPositionOutOfBounds(): void
    {
        $oid = '1.90'; // 'Z'
        $this->assertSame('Z', Oid::stringFromOid($oid, 0));
        $this->assertSame('', Oid::stringFromOid($oid, 1)); // no second string present
    }

    public function testStringFromOidZeroLengthSegment(): void
    {
        // three segments: 'ABC', '', 'Z'
        $oid = '3.65.66.67.0.1.90';
        $this->assertSame('ABC', Oid::stringFromOid($oid, 0));
        $this->assertSame('', Oid::stringFromOid($oid, 1));
        $this->assertSame('Z', Oid::stringFromOid($oid, 2));
    }

    public function testStringFromOidHighAsciiBytes(): void
    {
        // bytes > 127 should be packed as-is
        // example: 0xC3 0xBC (UTF-8 bytes for 'ü') length 2
        $oid = '2.195.188';
        $this->assertSame("\xC3\xBC", Oid::stringFromOid($oid));
    }
}
