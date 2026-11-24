<?php

namespace LibreNMS\Tests\Unit\OS;

use LibreNMS\OS;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class EntitySensorMibTest extends TestCase
{
    /**
     * @param  array{int, int}|null  $expected
     */
    #[DataProvider('scales')]
    public function testEntitySensorScale(?string $scale, int|string|null $precision, ?array $expected): void
    {
        $this->assertSame($expected, OS::entitySensorScale($scale, $precision));
    }

    /**
     * @return array<string, array{string|null, int|string|null, array{int, int}|null}>
     */
    public static function scales(): array
    {
        return [
            'units' => ['units', 0, [1, 1]],
            'precision' => [null, '2', [100, 1]],
            'milli' => ['milli', 1, [10000, 1]],
            'pico' => ['pico', 0, [1000000000000, 1]],
            'atto' => ['atto', 0, [1000000000000000000, 1]],
            'kilo with precision reduces' => ['kilo', 2, [1, 10]],
            'tera with precision fits' => ['tera', 3, [1, 1000000000]],
            'atto with precision does not fit' => ['atto', 1, null],
            'zepto does not fit' => ['zepto', 0, null],
            'tera does not fit' => ['tera', 0, null],
        ];
    }
}
