<?php

namespace LibreNMS\Tests\Unit\OS;

use LibreNMS\OS\Hytera;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HyteraTest extends TestCase
{
    #[DataProvider('floats')]
    public function testHexToFloat(string $input, ?string $expected): void
    {
        $this->assertSame($expected, Hytera::hexToFloat($input, 2));
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function floats(): array
    {
        return [
            'integer' => ['00 00 C0 41', '24.00'],
            'quoted' => ['"00 00 C0 41"', '24.00'],
            'fraction' => ['CD CC 4C 41', '12.80'],
            'small fraction' => ['9A 99 99 3F', '1.20'],
            'zero' => ['00 00 00 00', '0.00'],
            'negative' => ['00 00 48 C1', '-12.50'],
            'raw bytes' => ["\x00\x00\xC0\x41", '24.00'],
            'raw bytes with a quote' => ["\x00\x00\x22\x42", '40.50'],
            'quoted raw bytes with a quote' => ["\"\x00\x00\x22\x42\"", '40.50'],
            'raw bytes between quotes' => ["\x22\x00\x00\x22", number_format(unpack('g', "\x22\x00\x00\x22")[1], 2, '.', '')],
            'too short' => ['00 C0 41', null],
            'not hex' => ['00 00 G0 41', null],
        ];
    }
}
