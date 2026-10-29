<?php

namespace LibreNMS\Tests\Unit\Enum;

use LibreNMS\Enum\Severity;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SeverityTest extends TestCase
{
    #[DataProvider('fromSensorDescrProvider')]
    public function testFromSensorDescr(string $descr, Severity $expected): void
    {
        $this->assertSame($expected, Severity::fromSensorDescr($descr));
    }

    /**
     * @return array<string, array{string, Severity}>
     */
    public static function fromSensorDescrProvider(): array
    {
        return [
            'on' => ['On', Severity::Ok],
            'okay' => ['Okay', Severity::Ok],
            'ok' => ['Ok', Severity::Ok],
            'standby' => ['Standby', Severity::Warning],
            'idle' => ['Idle', Severity::Warning],
            'maintenance' => ['Maintenance', Severity::Warning],
            'under' => ['Under', Severity::Error],
            'over' => ['Over', Severity::Error],
            'apc relay close' => ['immediateCloseEMS', Severity::Error],
            'apc relay open' => ['immediateOpenEMS', Severity::Ok],
            'apc outlet on' => ['immediateOnEMS', Severity::Error],
            'apc outlet off' => ['immediateOffEMS', Severity::Ok],
            'unknown' => ['immediateBogusEMS', Severity::Unknown],
        ];
    }

    #[DataProvider('toGenericStateProvider')]
    public function testToGenericState(Severity $severity, int $expected): void
    {
        $this->assertSame($expected, $severity->toGenericState());
    }

    /**
     * @return array<string, array{Severity, int}>
     */
    public static function toGenericStateProvider(): array
    {
        return [
            'ok' => [Severity::Ok, 0],
            'warning' => [Severity::Warning, 1],
            'error' => [Severity::Error, 2],
            'unknown' => [Severity::Unknown, 3],
            'info' => [Severity::Info, 3],
            'notice' => [Severity::Notice, 3],
        ];
    }

    #[DataProvider('fromGenericStateProvider')]
    public function testFromGenericState(int $generic, Severity $expected): void
    {
        $this->assertSame($expected, Severity::fromGenericState($generic));
    }

    /**
     * @return array<string, array{int, Severity}>
     */
    public static function fromGenericStateProvider(): array
    {
        return [
            'ok' => [0, Severity::Ok],
            'warning' => [1, Severity::Warning],
            'error' => [2, Severity::Error],
            'unknown' => [3, Severity::Unknown],
            'negative' => [-1, Severity::Unknown],
            'out of range' => [4, Severity::Unknown],
        ];
    }
}
