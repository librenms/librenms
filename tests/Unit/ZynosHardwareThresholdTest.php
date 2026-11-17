<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Sensor;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ZynosHardwareThresholdTest extends TestCase
{
    public function testHardwareStatusUsesOperatingThresholdsRatherThanRecordedExtrema(): void
    {
        $definitions = Yaml::parseFile(resource_path('definitions/os_discovery/zynos.yaml'))['modules']['sensors'];
        $cases = [
            ['fanspeed', 'FanRpm', 'LowThreshold', 3670, 3671, 3564, 500, 499],
            ['temperature', 'Temperature', 'HighThreshold', 46, 46, 26, 85, 86],
            ['voltage', 'Voltage', 'LowThreshold', 12218, 15986, 12218, 10800, 10799],
        ];

        foreach ($cases as [$class, $prefix, $thresholdOid, $current, $maximum, $minimum, $threshold, $fault]) {
            $definition = $definitions[$class]['data'][0];
            $prefix = "ZYXEL-HW-MONITOR-MIB::zyHwMonitor$prefix";
            $cache = [$definition['oid'] => ['1' => [
                $prefix . 'CurrentValue' => $current,
                $prefix . 'MaxValue' => $maximum,
                $prefix . 'MinValue' => $minimum,
                $prefix . $thresholdOid => $threshold,
            ]]];
            $divisor = $definition['divisor'] ?? 1;
            $sensor = new Sensor(['sensor_class' => $class, 'sensor_current' => $current / $divisor]);

            foreach ([
                'high_limit' => 'sensor_limit',
                'warn_limit' => 'sensor_limit_warn',
                'low_limit' => 'sensor_limit_low',
                'low_warn_limit' => 'sensor_limit_low_warn',
            ] as $field => $attribute) {
                $value = YamlDiscovery::replaceValues($field, '1', 0, $definition, $cache);
                $sensor->$attribute = is_numeric($value) ? $value / $divisor : null;
            }

            $this->assertSame(Severity::Ok, $sensor->currentStatus(), "$class normal reading");
            $attribute = $class === 'temperature' ? 'sensor_limit' : 'sensor_limit_low';
            $this->assertEquals($threshold / $divisor, $sensor->$attribute, "$class operating threshold");
            $this->assertNull($sensor->sensor_limit_warn);
            $this->assertNull($sensor->sensor_limit_low_warn);
            $sensor->sensor_current = $fault / $divisor;
            $this->assertSame(Severity::Error, $sensor->currentStatus(), "$class threshold violation");
        }
    }
}
