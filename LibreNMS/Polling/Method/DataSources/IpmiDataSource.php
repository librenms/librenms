<?php

namespace LibreNMS\Polling\Method\DataSources;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Interfaces\Discovery\SensorsDiscovery;
use LibreNMS\Interfaces\Polling\SensorsPolling;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Util\Number;

/**
 * Sensors of the BMC. Discovery reads the sensors with their thresholds, polling reads the sensor data records the check fetched.
 * The ipmi_unit setting maps the units of the BMC to sensor classes, sensors are told apart by their description.
 */
final readonly class IpmiDataSource implements SensorsDiscovery, SensorsPolling
{
    public function __construct(
        private Device $device,
        private IpmiConfig $config,
        private ProbeResult $result,
    ) {
    }

    /**
     * @return Collection<int, Sensor>
     *
     * @throws \LibreNMS\Exceptions\IpmiConnectionFailed
     */
    public function discoverSensors(): Collection
    {
        $sensors = new Collection;
        $values = new Ipmitool($this->device, $this->config)->sensors();
        usort($values, fn ($a, $b) => $a[0] <=> $b[0]);
        $index = 0;

        foreach ($values as [$descr, $current, $unit, $state, $lowNonRecoverable, $lowLimit, $lowWarn, $highWarn, $highLimit]) {
            if ($current == 'na' || ! LibrenmsConfig::has("ipmi_unit.$unit")) {
                continue;
            }

            // units mapped to an empty class (discrete) still consume an index to keep existing sensor_index stable
            $sensorIndex = $index++;
            $class = LibrenmsConfig::get("ipmi_unit.$unit");
            if (empty($class)) {
                continue;
            }

            $sensors->push(new Sensor([
                'poller_type' => 'ipmi',
                'sensor_class' => $class,
                'sensor_oid' => $descr,
                'sensor_index' => $sensorIndex,
                'sensor_type' => 'ipmi',
                'sensor_descr' => $descr,
                'sensor_limit' => self::limit($highLimit),
                'sensor_limit_warn' => self::limit($highWarn),
                'sensor_limit_low' => self::limit($lowLimit),
                'sensor_limit_low_warn' => self::limit($lowWarn),
                'sensor_current' => Number::cast($current),
                'rrd_type' => 'GAUGE',
            ]));
        }

        return $sensors;
    }

    /**
     * @param  EloquentCollection<int, Sensor>  $sensors
     * @return array<int, int|float>
     */
    public function pollSensors(EloquentCollection $sensors): array
    {
        $byDescr = $sensors->groupBy('sensor_class')->map->keyBy('sensor_descr');
        $values = [];

        /** @var list<array{string, string, string, string, string}> $records */
        $records = $this->result->stat('sdr', []);
        foreach ($records as [$descr, $value, $unit]) {
            $sensor = $byDescr->get((string) LibrenmsConfig::get("ipmi_unit.$unit"))?->get(trim((string) $descr));
            if ($sensor === null) {
                continue;
            }

            // records can have hexadecimal values, like "93h"
            if (preg_match('/^([0-9A-Fa-f]+)h$/', (string) $value, $matches)) {
                $value = hexdec($matches[1]);
            }

            $values[$sensor->sensor_id] = Number::cast($value);
        }

        return $values;
    }

    private static function limit(string $limit): ?float
    {
        return $limit == 'na' ? null : (float) $limit;
    }
}
