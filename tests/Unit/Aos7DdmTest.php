<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\OS\Aos7;
use LibreNMS\Tests\TestCase;
use Mockery;

class Aos7DdmTest extends TestCase
{
    public function testBreakoutChannelsKeepLaneNamesAndPhysicalTransceiverAssociation(): void
    {
        // Interface names and RX values from the reported OS6900 polling output.
        $data = $this->labelDdmRows([
            1057 => '1/1/51A', 1058 => '1/1/51B',
            1059 => '1/1/51C', 1060 => '1/1/51D',
        ], [
            '1057.1' => -21249, '1057.2' => -2986,
            '1057.3' => -2691, '1057.4' => -2213,
        ]);

        foreach (['A', 'B', 'C', 'D'] as $offset => $suffix) {
            $index = '1057.' . ($offset + 1);
            $this->assertSame('1/1/51' . $suffix, YamlDiscovery::getValueFromData('aos7DdmPortName', $index, ['oid' => 'ALCATEL-IND1-PORT-MIB::ddmPortInfoTable'], $data));
        }
        $this->assertSame(-2986, $data['ALCATEL-IND1-PORT-MIB::ddmPortInfoTable']['1057.2']['ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower']);

        $yaml = \Symfony\Component\Yaml\Yaml::parseFile(base_path('resources/definitions/os_discovery/aos7.yaml'));
        foreach (['current', 'dbm', 'temperature', 'voltage'] as $class) {
            foreach ($yaml['modules']['sensors'][$class]['data'] as $definition) {
                if ($definition['oid'] !== 'ALCATEL-IND1-PORT-MIB::ddmPortInfoTable') {
                    continue;
                }
                $this->assertSame('1057', YamlDiscovery::fillValues('entPhysicalIndex', '1057.2', $definition, 2, $data, $definition['entPhysicalIndex']));
                $this->assertStringStartsWith('1/1/51B ', YamlDiscovery::fillValues('descr', '1057.2', $definition, 2, $data, $definition['descr']));
            }
        }
    }

    public function testMissingBreakoutInterfaceKeepsCageAssociation(): void
    {
        $data = $this->labelDdmRows([1049 => '1/1/49A'], ['1049.1' => 725, '1049.2' => 725]);
        $rows = $data['ALCATEL-IND1-PORT-MIB::ddmPortInfoTable'];
        $this->assertSame('1/1/49A', $rows['1049.1']['aos7DdmPortName']);
        $this->assertSame('1/1/49A Lane 2', $rows['1049.2']['aos7DdmPortName']);
    }

    /**
     * @param  array<int, string>  $names
     * @param  array<string, int>  $readings
     * @return array<string, array<int|string, array<string, int|string>>>
     */
    private function labelDdmRows(array $names, array $readings): array
    {
        $device = ['os' => 'aos7'];
        $os = Aos7::make($device);
        $this->assertInstanceOf(Aos7::class, $os);

        $ifNames = [];
        foreach ($names as $index => $name) {
            $ifNames["IF-MIB::ifName.$index"] = $name;
        }
        $query = Mockery::mock(SnmpQueryBuilder::class);
        $query->shouldReceive('cache')->once()->andReturnSelf();
        $query->shouldReceive('walk')->once()->with('IF-MIB::ifName')->andReturn(new SnmpResponse($ifNames));
        $this->app->instance(SnmpQueryBuilder::class, $query);

        $rows = [];
        foreach ($readings as $index => $value) {
            $rows[$index] = [
                'ALCATEL-IND1-PORT-MIB::ddmPortRxOpticalPower' => $value,
                // The reported walk repeats channel 1; mapping must use the index.
                'ALCATEL-IND1-PORT-MIB::ddmPortChannel' => 1,
            ];
        }

        return ['ALCATEL-IND1-PORT-MIB::ddmPortInfoTable' => $os->ddmRowsWithPortNames($rows)];
    }
}
