<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Util\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Counter64Test extends TestCase
{
    #[DataProvider('counters')]
    public function testLow32Bits(?string $value, int|float $expected): void
    {
        $this->assertSame($expected, Number::uint64Low32($value));
    }

    public static function counters(): array
    {
        return [
            ['0', 0], ['1', 1], ['65535', 65535], ['65536', 65536],
            ['2147483648', 2147483648], ['4294967295', 4294967295],
            ['4294967296', 0], ['4294967297', 1], ['8589934593', 1],
            ['9223372036854775807', 4294967295], ['9223372036854775808', 0],
            ['9223372036854775809', 1], ['18446744073709551615', 4294967295],
            ['000018446744073709551615', 4294967295], ['000000', 0],
            ['18446744071926153491', 2511569171], ['18446744072817541484', 3402957164],
            [" \t4294967297\r\n", 1], [null, 0], ['', 0], [' ', 0],
            ['-5', 0], ['+5', 0], ['123 abc', 0], ['abc', 0],
            ['1e3', 0], ['1.5', 0], ["12\0", 0], ['12 3', 0],
        ];
    }
}
