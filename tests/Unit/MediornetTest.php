<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

final class MediornetTest extends TestCase
{
    public function testUnavailablePowerSuppliesAreNotSkippedAndMapToWarnings(): void
    {
        $definition = Yaml::parseFile(self::basePath('resources/definitions/os_discovery/mediornet.yaml'));
        $checked = 0;

        foreach ($definition['modules']['sensors']['state']['data'] as $sensor) {
            if (! in_array($sensor['oid'], ['RIEDEL-MEDIORNET-MIB::psu1', 'RIEDEL-MEDIORNET-MIB::psu2'], true)) {
                continue;
            }

            $states = array_column($sensor['states'], 'generic', 'value');
            $this->assertSame([0 => 1, 1 => 0, 2 => 2], $states);
            $this->assertFalse(YamlDiscovery::canSkipItem(0, 0, $sensor, [], [0 => [$sensor['oid'] => 0]]));
            $this->assertFalse(YamlDiscovery::canSkipItem(2, 0, $sensor, [], [0 => [$sensor['oid'] => 2]]));
            $checked++;
        }

        $this->assertSame(2, $checked);
    }

    public function testGatewayTemperatureIncludesZeroAndNegativeReadings(): void
    {
        $definition = Yaml::parseFile(self::basePath('resources/definitions/os_discovery/mediornet.yaml'));

        foreach ($definition['modules']['sensors']['temperature']['data'] as $sensor) {
            if (($sensor['value'] ?? '') !== 'RIEDEL-MEDIORNET-MIB::cardTemperature') {
                continue;
            }

            foreach (['IP Gateway 8R', 'IP Gateway 4R4T', 'Future Gateway'] as $cardType) {
                foreach ([-5, 0, 45] as $temperature) {
                    $data = [0 => [
                        'RIEDEL-MEDIORNET-MIB::cardStatus' => 1,
                        'RIEDEL-MEDIORNET-MIB::cardType' => $cardType,
                        'RIEDEL-MEDIORNET-MIB::cardTemperature' => $temperature,
                    ]];
                    $this->assertFalse(YamlDiscovery::canSkipItem($temperature, 0, $sensor, [], $data));
                }
            }

            foreach (['Main Module', 'Video In Module', 'Video Out Module', 'Link Module 16'] as $cardType) {
                $data = [0 => [
                    'RIEDEL-MEDIORNET-MIB::cardStatus' => 1,
                    'RIEDEL-MEDIORNET-MIB::cardType' => $cardType,
                ]];
                $this->assertTrue(YamlDiscovery::canSkipItem(0, 0, $sensor, [], $data));
            }

            $data = [0 => [
                'RIEDEL-MEDIORNET-MIB::cardStatus' => 0,
                'RIEDEL-MEDIORNET-MIB::cardType' => 'IP Gateway 8R',
            ]];
            $this->assertTrue(YamlDiscovery::canSkipItem(0, 0, $sensor, [], $data));

            return;
        }

        $this->fail('No card temperature definition found');
    }

    public function testStoppedFanIsNotSkipped(): void
    {
        $definition = Yaml::parseFile(self::basePath('resources/definitions/os_discovery/mediornet.yaml'));
        $sensor = $definition['modules']['sensors']['fanspeed']['data'][0];

        $this->assertFalse(YamlDiscovery::canSkipItem(0, 0, $sensor, []));
    }
}
