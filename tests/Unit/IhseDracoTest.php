<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\OS\IhseDraco;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class IhseDracoTest extends TestCase
{
    public function testUnindexedAliasesAreSkippedWithoutLosingIndexZero(): void
    {
        $discovery = Yaml::parseFile(dirname(__DIR__, 2) . '/resources/definitions/os_discovery/ihse-draco.yaml');
        $guarded = 0;

        foreach ($discovery['modules']['sensors'] as $sensorClass) {
            foreach ($sensorClass['data'] as $definition) {
                if (! isset($definition['skip_values'])) {
                    continue;
                }

                $this->assertTrue(YamlDiscovery::canSkipItem(0, '', $definition, [], ['value' => 0]));
                $this->assertFalse(YamlDiscovery::canSkipItem(0, 0, $definition, [], ['value' => 0]));
                $this->assertFalse(YamlDiscovery::canSkipItem(0, 1, $definition, [], ['value' => 0]));
                $guarded++;
            }
        }

        $this->assertSame(4, $guarded);
    }

    #[DataProvider('slotStates')]
    public function testSlotStates(int|float|string $raw, int $expected): void
    {
        $this->assertSame($expected, IhseDraco::slotState($raw));
    }

    /**
     * @return array<string, array{int|float|string, int}>
     */
    public static function slotStates(): array
    {
        return [
            'empty' => [0, 0],
            'ready' => [7, 1],
            'active CPU' => [15, 2],
            'active IO' => [268435535, 2],
            'SNMP string' => ['268435535', 2],
            'poller numeric value' => [268435535.0, 2],
            'error overrides active' => [0x0400000f, 3],
            'on hold' => [0x4000000f, 4],
            'invalid' => [0x8000000f, 5],
            'error overrides hold' => [0x4400000f, 3],
            'invalid overrides error' => [0x8400000f, 5],
            'partial ready bits' => [3, 6],
        ];
    }

    #[DataProvider('portStates')]
    public function testPortStates(int|float|string $raw, int $expected): void
    {
        $this->assertSame($expected, IhseDraco::portState($raw));
    }

    /**
     * @return array<string, array{int|float|string, int}>
     */
    public static function portStates(): array
    {
        return [
            'unavailable' => [0, 0],
            'available' => [7, 1],
            'connected' => [127, 2],
            'observed connected' => [8319, 2],
            'observed connected extra flags' => [8831, 2],
            'SNMP string' => ['8831', 2],
            'poller numeric value' => [8831.0, 2],
            'error overrides connection' => [0x807f, 3],
            'error without connection' => [0x8000, 3],
            'partial connection' => [1, 4],
        ];
    }
}
