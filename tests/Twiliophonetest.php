<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Alert\Transport\Twiliophone;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;

final class TwiliophoneTest extends TestCase
{
    #[DataProvider('numberProvider')]
    public function testNormalize(string $input, ?string $expected): void
    {
        $normalize = new ReflectionMethod(Twiliophone::class, 'normalize');

        $this->assertSame($expected, $normalize->invoke(null, $input));
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function numberProvider(): array
    {
        return [
            'plain E.164' => ['+15055553333', '+15055553333'],
            'formatted E.164' => ['+1 (505) 555-3333', '+15055553333'],
            'dotted E.164' => ['+1.505.555.3333', '+15055553333'],
            'international' => ['+44 20 7946 0958', '+442079460958'],
            'surrounding whitespace' => ['  +15055553333  ', '+15055553333'],
            'no country code' => ['5055553333', null],
            'country code without plus' => ['15055553333', null],
            'extension with x' => ['+15055553333 x123', null],
            'extension with ext' => ['+1 505 555 3333 ext. 12', null],
            'extension with hash' => ['+15055553333#12', null],
            'leading zero after plus' => ['+05055553333', null],
            'too short' => ['+1234567', null],
            'too long' => ['+1234567890123456', null],
            'empty' => ['', null],
        ];
    }
}
