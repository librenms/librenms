<?php

/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

namespace LibreNMS\Tests\Unit;

use App\Http\Controllers\Widgets\TransceiverTableController;
use App\Models\Device;
use App\Models\Port;
use App\Models\Sensor;
use LibreNMS\Tests\TestCase;
use ReflectionMethod;

class TransceiverTableWidgetTest extends TestCase
{
    public function testReadingsLanesAndRendering(): void
    {
        app()->setLocale('en');
        $controller = new TransceiverTableController;
        $format = new ReflectionMethod($controller, 'formatReading');
        $lanes = new ReflectionMethod($controller, 'laneReadings');
        $groups = new ReflectionMethod($controller, 'portGroupIds');
        $port = new Port;
        $port->setRelation('device', new Device(['os' => 'junos']));
        $axos = new Port;
        $axos->setRelation('device', new Device(['os' => 'axos']));
        foreach ([-21 => 'Critical', -20 => 'Critical', -15 => 'Warning', -10 => 'OK', -3 => 'Warning', 0 => 'Critical', 1 => 'Critical'] as $value => $status) {
            $this->check($format->invoke($controller, $this->sensor(['sensor_current' => $value]), $port)['status'], $status, 'Threshold ' . $value);
        }
        $this->check($format->invoke($controller, $this->sensor(['sensor_current' => null]), $port)['status'], 'Unknown', 'Null value');
        $this->check($format->invoke($controller, $this->sensor(['sensor_limit_low' => null, 'sensor_limit_low_warn' => null, 'sensor_limit_warn' => null, 'sensor_limit' => null]), $port)['status'], 'No thresholds', 'Unset thresholds');
        $this->check($format->invoke($controller, $this->sensor(['sensor_current' => 0]), $port)['value'], '0.00 dBm', 'Valid zero dBm');
        $this->check($format->invoke($controller, $this->sensor(['sensor_current' => -60, 'sensor_index' => 'axosOltPonPortRxPower.123']), $axos)['value'], 'N/A (zero reported)', 'AXOS sentinel');
        $this->check($format->invoke($controller, $this->sensor(['sensor_current' => -60]), $port)['value'], '-60.00 dBm', 'Non-AXOS -60');
        $numbered = [];
        foreach ([3, 1, 0, 2] as $lane) {
            $numbered[] = $this->sensor(['sensor_index' => 'rx-123.' . $lane, 'sensor_current' => -10 - $lane]);
        }
        $numbered[] = $this->sensor(['sensor_current' => -1]); // aggregate must not overwrite lane 0
        $result = $lanes->invoke($controller, $numbered, $port);
        $this->check(count($result), 4, 'Four lanes and aggregate');
        $this->check($result[0]['value'], '-10.00 dBm', 'Lane 0 identity');
        $this->check($result[3]['value'], '-13.00 dBm', 'Lane 3 identity');
        $this->check(array_keys($lanes->invoke($controller, [$this->sensor()], $port)), [0], 'Single unnumbered sensor');
        $this->check($lanes->invoke($controller, [$this->sensor(), $this->sensor()], $port)[0]['value'], 'N/A', 'Ambiguous sensors');
        $this->check($lanes->invoke($controller, [], $port)[0]['value'], 'N/A', 'No sensor');
        $this->check(array_keys($lanes->invoke($controller, [$this->sensor(['sensor_index' => 'rx-123.7'])], $port)), [7], 'Preserve extra lane');
        $this->check($groups->invoke($controller, ['selected_port_group' => '4']), [4], 'Old single-group setting');
        $this->check($groups->invoke($controller, ['selected_port_group' => ['', '4', '7', '4']]), [4, 7], 'Multiple groups');
        $this->check($groups->invoke($controller, ['selected_port_group' => ['']]), [], 'Cleared groups');
        // Render the real Blade view: controller logic checks alone do not catch
        // null lane lists in the empty-table branch.
        foreach ([[], ['laneNumbers' => null], ['laneNumbers' => []], ['laneNumbers' => [0, 1, 2, 3]]] as $laneData) {
            $html = view('widgets.transceiver-table', array_merge(['rows' => [], 'notice' => null], $laneData))->render();
            $this->check(str_contains($html, 'colspan="7"'), true, 'Empty table has seven columns');
            $this->check(str_contains($html, 'Rx Power 3'), true, 'Default lane header');
        }
        $html = view('widgets.transceiver-table', ['rows' => [], 'notice' => 'Selected group missing'])->render();
        $this->check(str_contains($html, 'Selected group missing'), true, 'Missing group notice');
        $html = view('widgets.transceiver-table', [
            'notice' => null, 'laneNumbers' => [0, 1, 2, 3],
            'rows' => [[
                'device' => 'Example', 'port' => 'et-0/0/0', 'port_url' => '/example-port',
                'description' => '<test>',
                'readings' => [0 => $format->invoke($controller, $this->sensor(), $port)],
            ]],
        ])->render();
        $this->check(str_contains($html, '-10.00 dBm'), true, 'Populated row renders');
        $this->check(str_contains($html, '&lt;test&gt;'), true, 'Description is escaped');
        $this->check(str_contains($html, 'href="/example-port"'), true, 'Port URL renders');
    }

    private function check(mixed $actual, mixed $expected, string $name): void
    {
        $this->assertSame($expected, $actual, $name);
    }

    private function sensor(array $values = []): Sensor
    {
        return new Sensor(array_merge([
            'sensor_class' => 'dbm', 'sensor_current' => -10,
            'sensor_descr' => 'et-0/0/0 Rx Power', 'sensor_index' => 'rx-123',
            'sensor_limit_low' => -20, 'sensor_limit_low_warn' => -15,
            'sensor_limit_warn' => -3, 'sensor_limit' => 0,
        ], $values));
    }
}
