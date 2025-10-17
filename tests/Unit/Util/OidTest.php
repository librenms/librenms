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
        $this->assertSame('ABC', Oid::stringFromOid($oid)); // default 's' extracts first string
        $this->assertSame('ABC', Oid::stringFromOid($oid, 's')); // explicit
    }

    public function testStringFromOidMultiplePositions(): void
    {
        // two strings: 'ABC' and 'xy'
        $oid = '3.65.66.67.2.120.121';
        $this->assertSame('ABC', Oid::stringFromOid($oid, 's'));
        $this->assertSame('xy', Oid::stringFromOid($oid, 'ss')); // skip first string, extract second
    }

    public function testStringFromOidPositionOutOfBounds(): void
    {
        $oid = '1.90'; // 'Z'
        $this->assertSame('Z', Oid::stringFromOid($oid, 's'));
        $this->assertSame('', Oid::stringFromOid($oid, 'ss')); // no second string present
    }

    public function testStringFromOidZeroLengthSegment(): void
    {
        // three segments: 'ABC', '', 'Z'
        $oid = '3.65.66.67.0.1.90';
        $this->assertSame('ABC', Oid::stringFromOid($oid, 's'));
        $this->assertSame('', Oid::stringFromOid($oid, 'ss'));
        $this->assertSame('Z', Oid::stringFromOid($oid, 'sss'));
    }

    public function testOidWithCombinedNumericAndString(): void
    {
        // first two indices are numeric (3, 49), followed by a string of length 7: 'Pre-Amp'
        $oid = '3.49.7.80.114.101.45.65.109.112';
        $this->assertSame('Pre-Amp', Oid::stringFromOid($oid, 'nns'));
        // sanity checks of other formats
        $this->assertSame("1\x07P", Oid::stringFromOid($oid, 's'));   // first string length=3 -> 49,7,80 ("1\x07P")
        $this->assertSame('', Oid::stringFromOid($oid, 'ns'));  // interpreting 49 as length also fails
    }

    public function testStringFromOidHighAsciiBytes(): void
    {
        // bytes > 127 should be packed as-is
        // example: 0xC3 0xBC (UTF-8 bytes for 'ü') length 2
        $oid = '2.195.188';
        $this->assertSame("\xC3\xBC", Oid::stringFromOid($oid));
    }

    public function testStringFromOidEmpty(): void
    {
        // zero length string segment
        $oid = '0';
        $this->assertSame('', Oid::stringFromOid($oid));
    }

    public function testEncodeString(): void
    {
        $encoded = Oid::encodeString('ABC');
        $this->assertSame('3.65.66.67', $encoded->oid);
    }

    public function testEncodeStringEmpty(): void
    {
        $encoded = Oid::encodeString('');
        $this->assertSame('0', $encoded->oid);
    }

    public function testIsNumeric(): void
    {
        $this->assertTrue(Oid::of('1.3.6.1')->isNumeric());
        $this->assertTrue(Oid::of('.1.3.6.1')->isNumeric());
        $this->assertFalse(Oid::of('IF-MIB::ifDescr.0')->isNumeric());
        $this->assertFalse(Oid::of('ifDescr.0')->isNumeric());
    }

    public function testIsFullTextualOid(): void
    {
        $this->assertTrue(Oid::of('IF-MIB::ifDescr')->isFullTextualOid());
        // still matches even with instance suffix
        $this->assertTrue(Oid::of('IF-MIB::ifDescr.0')->isFullTextualOid());
        $this->assertFalse(Oid::of('ifDescr.0')->isFullTextualOid());
        $this->assertFalse(Oid::of('1.3.6.1')->isFullTextualOid());
    }

    public function testHasMibAndGetMib(): void
    {
        $this->assertTrue(Oid::of('IF-MIB::ifDescr.0')->hasMib());
        $this->assertSame('IF-MIB', Oid::of('IF-MIB::ifDescr.0')->getMib());
        $this->assertFalse(Oid::of('ifDescr.0')->hasMib());
        $this->assertSame('', Oid::of('ifDescr.0')->getMib());
    }

    public function testHasNumericRoot(): void
    {
        $this->assertTrue(Oid::of('1.3.6.1')->hasNumericRoot());
        $this->assertTrue(Oid::of('.1.3.6.1')->hasNumericRoot());
        $this->assertFalse(Oid::of('2.3.6.1')->hasNumericRoot());
        $this->assertFalse(Oid::of('IF-MIB::ifDescr')->hasNumericRoot());
    }

    public function testIsValid(): void
    {
        $this->assertTrue(Oid::of('1.3.6.1')->isValid('1.3.6.1'));
        $this->assertTrue(Oid::of('IF-MIB::ifDescr')->isValid('IF-MIB::ifDescr'));
        $this->assertFalse(Oid::of('ifDescr')->isValid('ifDescr'));
    }

    public function testHasNumericStatic(): void
    {
        $this->assertTrue(Oid::hasNumeric(['IF-MIB::ifDescr', '1.3.6.1']));
        $this->assertFalse(Oid::hasNumeric(['IF-MIB::ifDescr', 'ifName.0']));
    }

    public function testToStringCastsToOriginal(): void
    {
        $this->assertSame('1.2.3', (string) Oid::of('1.2.3'));
    }
}
