<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Util\PortUi;
use PHPUnit\Framework\TestCase;

class PortUiTest extends TestCase
{
    public function testPortNameNormalization(): void
    {
        $this->assertEquals('gi1/0/1', PortUi::normalizePortName('GigabitEthernet1/0/1'));
        $this->assertEquals('te1/0/49', PortUi::normalizePortName('TenGigabitEthernet1/0/49'));
        $this->assertEquals('fa0/1', PortUi::normalizePortName('FastEthernet0/1'));
        $this->assertEquals('twe1/0/51', PortUi::normalizePortName('TwentyFiveGigE1/0/51'));
        $this->assertEquals('fo1/0/1', PortUi::normalizePortName('FortyGigabitEthernet1/0/1'));
        $this->assertEquals('hu1/0/1', PortUi::normalizePortName('HundredGigE1/0/1'));
        $this->assertEquals('eth1/1', PortUi::normalizePortName('Ethernet1/1'));
    }

    protected function getTemplatePath(string $subpath): string
    {
        return dirname(__DIR__, 3) . '/resources/views/device/portui/' . $subpath;
    }

    public function testAruba6100TemplateParsing(): void
    {
        $path = $this->getTemplatePath('aruba/6100_48g_class4_poe_4sfp+_370w_switch.json');
        $this->assertFileExists($path);

        $json = json_decode(file_get_contents($path), true);
        $this->assertIsArray($json);

        $transform = PortUi::generateTransformFromTemplate($json);
        $this->assertNotEmpty($transform);
        $this->assertCount(1, $transform); // 1 module

        $blocks = $transform[0];
        $this->assertCount(2, $blocks); // Block 0 (SFP) + Block 1 (Copper)

        // Block 0: 4 ports with height 2 -> 2 columns, each with 2 ports
        $this->assertCount(2, $blocks[0]);

        // Block 1: 48 ports with height 2 -> 24 columns, each with 2 ports
        $this->assertCount(24, $blocks[1]);
    }

    public function testAruba6300mTemplateParsing(): void
    {
        $path = $this->getTemplatePath('aruba/6300m_48-port_1gbe_and_4-port_sfp56_switch.json');
        $this->assertFileExists($path);

        $json = json_decode(file_get_contents($path), true);
        $transform = PortUi::generateTransformFromTemplate($json);
        $this->assertCount(2, $transform[0]);
    }

    public function testCiscoAsr920TemplateParsing(): void
    {
        $path = $this->getTemplatePath('cisco/ciscoasr92024szm.json');
        $this->assertFileExists($path);

        $json = json_decode(file_get_contents($path), true);
        $transform = PortUi::generateTransformFromTemplate($json);
        $this->assertNotEmpty($transform);
        $this->assertCount(6, $transform[0]); // 6 blocks
    }

    public function testFortinet60fTemplateParsing(): void
    {
        $path = $this->getTemplatePath('fortinet/fgt_60f.json');
        $this->assertFileExists($path);

        $json = json_decode(file_get_contents($path), true);
        $transform = PortUi::generateTransformFromTemplate($json);
        $this->assertNotEmpty($transform);
        $this->assertCount(5, $transform[0]); // 5 blocks
    }

    public function testBuildVisualGrid(): void
    {
        $template = [
            'front' => [
                [
                    'height' => '2',
                    'block' => '0',
                    'count' => '4',
                    'type' => 'sfp',
                    'start' => '1/1/49',
                ],
                [
                    'height' => '2',
                    'block' => '1',
                    'count' => '4',
                    'type' => 'cu',
                    'start' => '1/1/1',
                ],
            ],
        ];

        $transform = PortUi::generateTransformFromTemplate($template);

        $mockPorts = [
            '1/1/1' => (object)[
                'port_id' => 1,
                'ifName' => '1/1/1',
                'ifDescr' => 'Port 1',
                'ifAdminStatus' => 'up',
                'ifOperStatus' => 'up',
                'ifSpeed' => 1000000000,
                'ifVlan' => 10,
            ],
            '1/1/49' => (object)[
                'port_id' => 49,
                'ifName' => '1/1/49',
                'ifDescr' => 'Port 49',
                'ifAdminStatus' => 'up',
                'ifOperStatus' => 'down',
                'ifSpeed' => 10000000000,
                'ifVlan' => 20,
            ],
        ];

        $grid = PortUi::buildVisualGrid([1 => 'SERIAL12345'], $transform, $mockPorts, []);
        $this->assertArrayHasKey('switches', $grid);
        $this->assertCount(1, $grid['switches']);

        $switch = $grid['switches'][0];
        $this->assertEquals(1, $switch['id']);
        $this->assertEquals('SERIAL12345', $switch['serial']);

        $blocks = $switch['modules'][0]['blocks'];
        $this->assertCount(2, $blocks);

        // Check SFP block row cells
        $firstSfpCell = $blocks[0]['rows'][0][0];
        $this->assertNotNull($firstSfpCell);
        $this->assertEquals('sfp', $firstSfpCell['type']);
        $this->assertEquals('1/1/49', $firstSfpCell['ifName']);
        $this->assertEquals('49', $firstSfpCell['label']);
        $this->assertEquals('down', $firstSfpCell['operStatus']);
    }
}
