<?php

namespace LibreNMS\Tests\Unit\Polling;

use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Tests\TestCase;
use Mockery;

final class RecordSensorDataTest extends TestCase
{
    public function testEntitySensorSentinelsDoNotReachDatastore(): void
    {
        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')
            ->twice()
            ->withArgs(static fn ($device, $measurement, $tags, $fields) => $measurement === 'sensor'
                && $tags['sensor_type'] === 'entity-sensor'
                && $tags['sensor_class'] === 'temperature'
                && $fields === ['sensor' => 23]);

        $this->app->instance('Datastore', $datastore);

        $device = [
            'device_id' => 1,
            'hostname' => 'test-device',
            'os' => 'arista_eos',
        ];

        $sensor = [
            'sensor_id' => 1,
            'sensor_type' => 'entity-sensor',
            'sensor_class' => 'temperature',
            'sensor_descr' => 'PSU 2',
            'sensor_index' => '100721101',
            'sensor_oid' => '.1.3.6.1.2.1.99.1.1.1.4.100721101',
            'poller_type' => 'snmp',
            'sensor_current' => 23,
            'sensor_divisor' => 10,
            'sensor_multiplier' => 1,
            'sensor_limit_low' => 11,
            'sensor_limit' => 70,
            'sensor_alert' => 1,
            'rrd_type' => 'GAUGE',
        ];

        // Model normal -> underflow -> overflow -> normal polling responses.
        // Only the two normal readings should reach the datastore.
        $readings = [230, -1000000000, 1000000000, 230];
        $samples = array_map(static fn (int $value): array => [
            ...$sensor,
            'new_value' => $value,
        ], $readings);

        record_sensor_data($device, $samples);
    }
}
