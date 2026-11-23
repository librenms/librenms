<?php

namespace LibreNMS\Tests\Unit\Util;

use App\Models\Port;
use Illuminate\Support\Collection;
use LibreNMS\Enum\IfOperStatus;
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

    public function testInverseTemplateParsing(): void
    {
        $template = [
            'front' => [
                [
                    'height' => '2',
                    'inverse' => '1',
                    'block' => '0',
                    'count' => '2',
                    'type' => 'sfp',
                    'start' => 'Gi0/0/0',
                ],
                [
                    'height' => '2',
                    'inverse' => '0',
                    'block' => '1',
                    'count' => '2',
                    'type' => 'sfp',
                    'start' => 'Gi0/0/2',
                ],
            ],
        ];

        $transform = PortUi::generateTransformFromTemplate($template);

        // Block 0 with inverse=1: port 0 (Gi0/0/0) is at bottom (row 1), port 1 (Gi0/0/1) is at top (row 0)
        $inverseCol = $transform[0][0][0];
        $this->assertEquals(['Gi0/0/1' => 'sfp'], $inverseCol[0]);
        $this->assertEquals(['Gi0/0/0' => 'sfp'], $inverseCol[1]);

        // Block 1 with inverse=0: port 0 (Gi0/0/2) is at top (row 0), port 1 (Gi0/0/3) is at bottom (row 1)
        $normalCol = $transform[0][1][0];
        $this->assertEquals(['Gi0/0/2' => 'sfp'], $normalCol[0]);
        $this->assertEquals(['Gi0/0/3' => 'sfp'], $normalCol[1]);
    }

    public function testTransformPortsAutoMultiSwitchStack(): void
    {
        $ports = new Collection([
            (object)[
                'ifName' => 'GigabitEthernet1/0/1',
                'ifType' => 'ethernetCsmacd',
                'ifDescr' => 'GigabitEthernet1/0/1',
            ],
            (object)[
                'ifName' => 'GigabitEthernet1/0/2',
                'ifType' => 'ethernetCsmacd',
                'ifDescr' => 'GigabitEthernet1/0/2',
            ],
            (object)[
                'ifName' => 'GigabitEthernet2/0/1',
                'ifType' => 'ethernetCsmacd',
                'ifDescr' => 'GigabitEthernet2/0/1',
            ],
            (object)[
                'ifName' => 'GigabitEthernet2/0/2',
                'ifType' => 'ethernetCsmacd',
                'ifDescr' => 'GigabitEthernet2/0/2',
            ],
        ]);

        $transform = PortUi::transformPortsAuto($ports, 2, []);

        // Two switches should yield 2 blocks in module 0
        $this->assertCount(2, $transform[0]);

        $indexedPorts = [
            'GigabitEthernet1/0/1' => (object)[
                'port_id' => 1,
                'ifName' => 'GigabitEthernet1/0/1',
                'ifDescr' => 'Gi1/0/1',
                'ifAdminStatus' => 'up',
                'ifOperStatus' => 'up',
            ],
            'GigabitEthernet1/0/2' => (object)[
                'port_id' => 2,
                'ifName' => 'GigabitEthernet1/0/2',
                'ifDescr' => 'Gi1/0/2',
                'ifAdminStatus' => 'up',
                'ifOperStatus' => 'down',
            ],
            'GigabitEthernet2/0/1' => (object)[
                'port_id' => 3,
                'ifName' => 'GigabitEthernet2/0/1',
                'ifDescr' => 'Gi2/0/1',
                'ifAdminStatus' => 'up',
                'ifOperStatus' => 'up',
            ],
            'GigabitEthernet2/0/2' => (object)[
                'port_id' => 4,
                'ifName' => 'GigabitEthernet2/0/2',
                'ifDescr' => 'Gi2/0/2',
                'ifAdminStatus' => 'down',
                'ifOperStatus' => 'down',
            ],
        ];

        $grid = PortUi::buildVisualGrid([1 => 'SER1', 2 => 'SER2'], $transform, $indexedPorts, []);
        $this->assertCount(2, $grid['switches']);
        $this->assertEquals(1, $grid['switches'][0]['id']);
        $this->assertEquals(2, $grid['switches'][1]['id']);
        $this->assertTrue($grid['hasMultipleSwitches']);

        // Switch 1 has 1/0/1 and 1/0/2
        $sw1Rows = $grid['switches'][0]['modules'][0]['blocks'][0]['rows'];
        $this->assertEquals('GigabitEthernet1/0/1', $sw1Rows[0][0]['ifName']);
        $this->assertEquals('GigabitEthernet1/0/2', $sw1Rows[1][0]['ifName']);

        // Switch 2 has 2/0/1 and 2/0/2
        $sw2Rows = $grid['switches'][1]['modules'][0]['blocks'][0]['rows'];
        $this->assertEquals('GigabitEthernet2/0/1', $sw2Rows[0][0]['ifName']);
        $this->assertEquals('GigabitEthernet2/0/2', $sw2Rows[1][0]['ifName']);
    }

    public function testStatusEnumHandling(): void
    {
        $template = [
            'front' => [
                [
                    'height' => '2',
                    'block' => '0',
                    'count' => '2',
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
                'ifAdminStatus' => IfOperStatus::Up,
                'ifOperStatus' => IfOperStatus::Up,
            ],
            '1/1/2' => (object)[
                'port_id' => 2,
                'ifName' => '1/1/2',
                'ifDescr' => 'Port 2',
                'ifAdminStatus' => IfOperStatus::Down,
                'ifOperStatus' => IfOperStatus::Down,
            ],
        ];

        $grid = PortUi::buildVisualGrid([1 => 'SERIAL1'], $transform, $mockPorts, []);
        $rows = $grid['switches'][0]['modules'][0]['blocks'][0]['rows'];

        $this->assertEquals('up', $rows[0][0]['adminStatus']);
        $this->assertEquals('up', $rows[0][0]['operStatus']);

        $this->assertEquals('down', $rows[1][0]['adminStatus']);
        $this->assertEquals('down', $rows[1][0]['operStatus']);
    }
}
