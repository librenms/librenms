<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;

class Aos7DdmTest extends TestCase
{
    public function testBreakoutChannelsUseTheirOwnInterfaces(): void
    {
        // Interface names and RX values from the reported OS6900 polling output.
        $data = $this->cache([
            1057 => '1/1/51A', 1058 => '1/1/51B',
            1059 => '1/1/51C', 1060 => '1/1/51D',
        ], [
            '1057.1' => -21249, '1057.2' => -2986,
            '1057.3' => -2691, '1057.4' => -2213,
        ]);

        foreach (['A', 'B', 'C', 'D'] as $offset => $suffix) {
            $index = '1057.' . ($offset + 1);
            $this->assertSame('1/1/51' . $suffix, YamlDiscovery::getValueFromData('aos7DdmPortName', $index, ['oid' => 'ALCATEL-IND1-PORT-MIB::ddmPortInfoTable'], $data));
            $this->assertEquals(1057 + $offset, YamlDiscovery::getValueFromData('aos7DdmIfIndex', $index, ['oid' => 'ALCATEL-IND1-PORT-MIB::ddmPortInfoTable'], $data));
        }
        $this->assertSame(-2986, $data['ALCATEL-IND1-PORT-MIB::ddmPortInfoTable']['1057.2']['ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower']);

        $yaml = \Symfony\Component\Yaml\Yaml::parseFile(base_path('resources/definitions/os_discovery/aos7.yaml'));
        foreach (['current', 'dbm', 'temperature', 'voltage'] as $class) {
            foreach ($yaml['modules']['sensors'][$class]['data'] as $definition) {
                if ($definition['oid'] !== 'ALCATEL-IND1-PORT-MIB::ddmPortInfoTable') {
                    continue;
                }
                $this->assertSame('1058', YamlDiscovery::fillValues('entPhysicalIndex', '1057.2', $definition, 2, $data, $definition['entPhysicalIndex']));
                $this->assertStringStartsWith('1/1/51B ', YamlDiscovery::fillValues('descr', '1057.2', $definition, 2, $data, $definition['descr']));
            }
        }
    }

    public function testMissingBreakoutInterfaceKeepsCageAssociation(): void
    {
        $data = $this->cache([1049 => '1/1/49A'], ['1049.1' => 725, '1049.2' => 725]);
        $rows = $data['ALCATEL-IND1-PORT-MIB::ddmPortInfoTable'];
        $this->assertSame('1/1/49A', $rows['1049.1']['aos7DdmPortName']);
        $this->assertSame('1/1/49A Lane 2', $rows['1049.2']['aos7DdmPortName']);
        $this->assertEquals(1049, $rows['1049.2']['aos7DdmIfIndex']);
    }

    /**
     * @param  array<int, string>  $names
     * @param  array<string, int>  $readings
     * @return array<string, array<int|string, array<string, int|string>>>
     */
    private function cache(array $names, array $readings): array
    {
        $device = ['os' => 'aos7'];
        $os = OS::make($device);
        $data = [];
        foreach ($names as $index => $name) {
            $data['IF-MIB::ifName'][$index] = ['IF-MIB::ifName' => $name];
        }
        foreach ($readings as $index => $value) {
            $data['ALCATEL-IND1-PORT-MIB::ddmPortInfoTable'][$index] = [
                'ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower' => $value,
                // The reported walk repeats channel 1; mapping must use the index.
                'ALCATEL-IND1-PORT-MIB::ddmPortChannel' => 1,
            ];
        }
        (new \ReflectionProperty(OS::class, 'pre_cache'))->setValue($os, $data);

        return $os->preCache();
    }
}
