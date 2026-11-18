<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Util\AristaOpticalLabel;
use PHPUnit\Framework\TestCase;

class AristaOpticalLabelTest extends TestCase
{
    /**
     * @param  list<int>  $suffixes
     * @return array{array<int, array<string, mixed>>, list<array<string, mixed>>}
     */
    private function inventory(array $suffixes = [1, 3, 5, 7]): array
    {
        $entities = [100 => [
            'entPhysicalClass' => 'container',
            'entPhysicalName' => 'Xcvr for Ethernet25: PLR4',
            'entPhysicalDescr' => '400GBASE-PLR4',
            'entPhysicalContainedIn' => 0,
        ]];
        $ports = [];
        foreach ($suffixes as $lane => $suffix) {
            $entities[200 + $lane] = [
                'entPhysicalClass' => 'module',
                'entPhysicalName' => "Lane $lane for Xcvr for Ethernet25",
                'entPhysicalParentRelPos' => $lane,
                'entPhysicalContainedIn' => 100,
            ];
            $entities[300 + $lane] = ['entPhysicalClass' => 'sensor', 'entPhysicalContainedIn' => 200 + $lane];
            $ports[] = ['ifName' => "Ethernet25/$suffix", 'ifIndex' => 1000 + $lane, 'ifSpeed' => 100000000000];
        }

        return [$entities, $ports];
    }

    public function testVerifiedLayoutsAndIdempotence(): void
    {
        foreach ([[1, 3, 5, 7], [1, 2, 3, 4]] as $suffixes) {
            [$entities, $ports] = $this->inventory($suffixes);
            foreach ($suffixes as $lane => $suffix) {
                foreach (['RX', 'TX'] as $direction) {
                    $expected = "DOM $direction Power For Ethernet25/$suffix (optical lane " . ($lane + 1) . ')';
                    $raw = "DOM $direction Power  For Ethernet25/" . ($lane + 1);
                    $this->assertSame($expected, AristaOpticalLabel::rewrite($raw, 300 + $lane, $entities, $ports));
                    $this->assertSame($expected, AristaOpticalLabel::rewrite($expected, 300 + $lane, $entities, $ports));
                }
            }
        }
    }

    public function testAmbiguousInventoriesPreserveOriginalLabel(): void
    {
        $raw = 'DOM RX Power For Ethernet25/2';
        foreach (['missing_parent', 'cycle', 'duplicate_lane', 'missing_lane', 'wrong_media', 'missing_port', 'duplicate_index', 'wrong_speed', 'extra_parent', 'module_sensor', 'nested_interface', 'duplicate_interface'] as $case) {
            [$entities, $ports] = $this->inventory();
            $index = 301;
            switch ($case) {
                case 'missing_parent': unset($entities[201]); break;
                case 'cycle': $entities[301]['entPhysicalContainedIn'] = 301; break;
                case 'duplicate_lane': $entities[250] = $entities[201]; break;
                case 'missing_lane': unset($entities[202]); break;
                case 'wrong_media': $entities[100]['entPhysicalDescr'] = '100GBASE-LR4'; break;
                case 'missing_port': array_pop($ports); break;
                case 'duplicate_index': $ports[1]['ifIndex'] = $ports[0]['ifIndex']; break;
                case 'wrong_speed': $ports[1]['ifSpeed'] = 400000000000; break;
                case 'extra_parent': $ports[] = ['ifName' => 'Ethernet25', 'ifIndex' => 5, 'ifSpeed' => 400000000000]; break;
                case 'module_sensor': $index = 100; break;
                case 'nested_interface': $ports[1]['ifName'] = 'Ethernet25/3/1'; break;
                case 'duplicate_interface': $ports[1]['ifName'] = $ports[0]['ifName']; break;
            }
            $this->assertSame($raw, AristaOpticalLabel::rewrite($raw, $index, $entities, $ports), $case);
        }
    }

    public function testStandaloneAndModuleSensorsRemainUnchanged(): void
    {
        [$entities, $ports] = $this->inventory();
        $raw = 'DOM RX Power For Ethernet25/2';
        $this->assertSame($raw, AristaOpticalLabel::rewrite($raw, 301, $entities, [
            ['ifName' => 'Ethernet25/1', 'ifIndex' => 1, 'ifSpeed' => 400000000000],
        ]));
        $temperature = 'DOM Temperature For Ethernet25';
        $this->assertSame($temperature, AristaOpticalLabel::rewrite($temperature, 100, $entities, $ports));
        $this->assertSame('DOM RX Power For Ethernet99/2', AristaOpticalLabel::rewrite('DOM RX Power For Ethernet99/2', 301, $entities, $ports));
    }

    public function testAdditionalElectricalLanesDoNotChangeOpticalMapping(): void
    {
        [$entities, $ports] = $this->inventory();
        foreach (range(4, 7) as $lane) {
            $entities[200 + $lane] = [
                'entPhysicalClass' => 'module', 'entPhysicalName' => "Lane $lane for Xcvr for Ethernet25",
                'entPhysicalParentRelPos' => $lane, 'entPhysicalContainedIn' => 100,
            ];
        }
        $this->assertSame('DOM RX Power For Ethernet25/3 (optical lane 2)', AristaOpticalLabel::rewrite('DOM RX Power For Ethernet25/2', 301, $entities, $ports));
    }

    public function testEightByFiftyGigabitLayoutsAreNotInferred(): void
    {
        [$entities, $ports] = $this->inventory(range(1, 8));
        foreach ($ports as &$port) {
            $port['ifSpeed'] = 50000000000;
        }
        unset($port);
        $raw = 'DOM RX Power For Ethernet25/2';
        $this->assertSame($raw, AristaOpticalLabel::rewrite($raw, 301, $entities, $ports));
        $entities[100]['entPhysicalDescr'] = '400GBASE-SR8';
        $this->assertSame($raw, AristaOpticalLabel::rewrite($raw, 301, $entities, $ports));
    }
}
