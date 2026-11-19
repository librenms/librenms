<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;

class Aos7DdmTest extends TestCase
{
    public function testOpticalSensorsNameTheLaneAndReferenceTheCagePort(): void
    {
        $device = ['os' => 'aos7'];
        $sensors = OS::make($device)->getDiscovery('sensors');
        $data = ['IF-MIB::ifName' => [1057 => ['IF-MIB::ifName' => '1/1/51A']]];

        foreach (['current', 'dbm', 'temperature', 'voltage'] as $class) {
            foreach ($sensors[$class]['data'] as $definition) {
                if ($definition['oid'] !== 'ALCATEL-IND1-PORT-MIB::ddmPortInfoTable') {
                    continue;
                }

                $this->assertStringStartsWith('1/1/51A Lane 2 ', YamlDiscovery::replaceValues('descr', '1057.2', 1, $definition, $data));
                $this->assertSame('1057', YamlDiscovery::replaceValues('entPhysicalIndex', '1057.2', 1, $definition, $data));
            }
        }
    }
}
