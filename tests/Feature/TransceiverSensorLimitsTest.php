<?php

namespace LibreNMS\Tests\Feature;

use App\Discovery\Sensor as SensorDiscovery;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\Sensor;
use App\Models\Transceiver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class TransceiverSensorLimitsTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    private Port $port;

    private Transceiver $transceiver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->device = Device::factory()->create(['os' => 'junos']);
        $this->port = Port::factory()->for($this->device)->create(['ifIndex' => 507, 'ifName' => 'et-0/0/0']);
        $this->transceiver = Transceiver::factory()->create([
            'device_id' => $this->device->device_id, 'port_id' => $this->port->port_id,
            'index' => '507', 'serial' => 'SERIAL-A',
        ]);
        \DeviceCache::setPrimary($this->device->device_id);
    }

    private function sensor(string $class = 'temperature', string $index = 'rx-507', string $custom = 'No', int $portIndex = 507): Sensor
    {
        return Sensor::factory()->for($this->device)->createQuietly([
            'sensor_class' => $class, 'sensor_type' => 'junos', 'sensor_index' => $index,
            'poller_type' => 'snmp', 'sensor_custom' => $custom, 'sensor_current' => 24,
            'sensor_limit' => 80, 'sensor_limit_warn' => 75, 'sensor_limit_low_warn' => -10, 'sensor_limit_low' => -15,
            'entPhysicalIndex' => (string) $portIndex, 'entPhysicalIndex_measured' => 'ports',
        ])->refresh();
    }

    public function test_changed_serial_resets_automatic_limits_but_preserves_manual_and_other_ports(): void
    {
        $temperature = $this->sensor();
        $lane = $this->sensor('dbm', 'rx-507.0');
        $frequency = $this->sensor('frequency', 'jnxPMCurCarFreqOffset.507');
        $manual = $this->sensor('dbm', 'tx-507.1', 'Yes');
        $otherPort = $this->sensor(index: 'rx-508', portIndex: 508);
        $this->transceiver->serial = 'SERIAL-B';
        $this->transceiver->save();

        $this->assertSame('Reset', $temperature->refresh()->sensor_custom);
        $this->assertSame('Reset', $lane->refresh()->sensor_custom);
        $this->assertSame('Reset', $frequency->refresh()->sensor_custom);
        $this->assertSame('Yes', $manual->refresh()->sensor_custom);
        $this->assertSame('No', $otherPort->refresh()->sensor_custom);
        $event = Eventlog::where('device_id', $this->device->device_id)->where('type', 'interface')->latest('event_id')->firstOrFail();
        $this->assertStringContainsString('SERIAL-A', $event->message);
        $this->assertStringContainsString('SERIAL-B', $event->message);
        $this->assertStringContainsString('automatic sensor limit reset queued for 3 sensors', $event->message);
        $this->assertSame(1, Eventlog::where('device_id', $this->device->device_id)->where('message', 'like', 'Transceiver changed%')->count());

        $discovery = new SensorDiscovery($this->device);
        foreach ([$temperature, $lane, $frequency, $manual, $otherPort] as $sensor) {
            $discovery->discover($sensor->replicate(['sensor_custom'])->forceFill([
                'sensor_current' => 40, 'sensor_limit' => null, 'sensor_limit_warn' => null,
                'sensor_limit_low_warn' => null, 'sensor_limit_low' => null,
            ]));
        }
        foreach (['temperature', 'dbm', 'frequency'] as $class) {
            $discovery->sync(sensor_class: $class, poller_type: 'snmp');
        }
        $this->assertEquals(60, $temperature->refresh()->sensor_limit);
        $this->assertEquals(30, $temperature->sensor_limit_low);
        $this->assertNull($temperature->sensor_limit_warn);
        $this->assertSame('No', $temperature->sensor_custom);
        $this->assertNull($lane->refresh()->sensor_limit);
        $this->assertNull($lane->sensor_limit_low_warn);
        $this->assertEquals(42, $frequency->refresh()->sensor_limit);
        $manual->refresh();
        $this->assertEquals([80, 75, -10, -15], [$manual->sensor_limit, $manual->sensor_limit_warn, $manual->sensor_limit_low_warn, $manual->sensor_limit_low]);
        $this->assertEquals(80, $otherPort->refresh()->sensor_limit);
    }

    public function test_loaded_sensor_relation_does_not_prevent_reset(): void
    {
        $sensor = $this->sensor();
        $device = \DeviceCache::get($this->device->device_id);
        $device->load('sensors');
        $this->transceiver->serial = 'SERIAL-B';
        $this->transceiver->save();
        $discovered = $sensor->replicate(['sensor_custom'])->forceFill([
            'sensor_current' => 40, 'sensor_limit' => null, 'sensor_limit_warn' => null,
            'sensor_limit_low_warn' => null, 'sensor_limit_low' => null,
        ]);
        $discovery = new SensorDiscovery($device);
        $discovery->discover($discovered)->sync(sensor_class: 'temperature', poller_type: 'snmp');
        $this->assertEquals(60, $sensor->refresh()->sensor_limit);
    }

    /** @return array<string, array{?string, ?string}> */
    public static function unchangedOrMissingSerials(): array
    {
        return [
            'same' => ['SERIAL-A', 'SERIAL-A'],
            'whitespace only difference' => ['SERIAL-A', ' SERIAL-A '],
            'old missing' => [null, 'SERIAL-B'],
            'old blank' => ['', 'SERIAL-B'],
            'new missing' => ['SERIAL-A', null],
            'new blank' => ['SERIAL-A', ''],
            'new whitespace' => ['SERIAL-A', '  '],
        ];
    }

    #[DataProvider('unchangedOrMissingSerials')]
    public function test_unchanged_or_missing_serial_does_not_reset(?string $old, ?string $new): void
    {
        $this->transceiver->serial = $old;
        $this->transceiver->saveQuietly();
        $sensor = $this->sensor();
        $this->transceiver->serial = $new;
        $this->transceiver->save();
        $this->assertSame('No', $sensor->refresh()->sensor_custom);
        $this->assertEquals(80, $sensor->sensor_limit);
    }

    public function test_other_operating_systems_and_metadata_changes_do_not_reset(): void
    {
        $sensor = $this->sensor();
        $this->transceiver->model = 'RENAMED';
        $this->transceiver->save();
        $this->assertSame('No', $sensor->refresh()->sensor_custom);
        $this->device->os = 'ios';
        $this->device->save();
        \DeviceCache::flush();
        $this->transceiver->serial = 'SERIAL-B';
        $this->transceiver->save();
        $this->assertSame('No', $sensor->refresh()->sensor_custom);
        $this->assertEquals(80, $sensor->sensor_limit);
    }

    public function test_manual_only_changes_do_not_claim_a_reset_was_queued(): void
    {
        $sensor = $this->sensor(custom: 'Yes');
        $this->transceiver->serial = 'SERIAL-B';
        $this->transceiver->save();
        $event = Eventlog::where('device_id', $this->device->device_id)->where('type', 'interface')->latest('event_id')->firstOrFail();
        $this->assertStringContainsString('SERIAL-B', $event->message);
        $this->assertStringNotContainsString('reset queued', $event->message);
        $this->assertSame('Yes', $sensor->refresh()->sensor_custom);
    }

    public function test_resuming_a_stale_poll_does_not_clear_reset_or_restore_old_limits(): void
    {
        $sensor = $this->sensor();
        $sensor->sensor_limit = 44;
        $sensor->saveQuietly();
        $snapshot = $sensor->toArray();
        $snapshot['new_value'] = 60;
        $snapshot['sensor_divisor'] = 1;
        $snapshot['sensor_multiplier'] = 1;
        $datastore = Mockery::mock();
        $datastore->shouldReceive('put')->twice();
        $this->app->instance('Datastore', $datastore);

        $this->transceiver->serial = 'SERIAL-B';
        $this->transceiver->save();
        // The poll started before discovery noticed the replacement.
        record_sensor_data($this->device->attributesToArray(), [$snapshot]);
        $this->assertSame('Reset', $sensor->refresh()->sensor_custom);

        $discovered = $sensor->replicate(['sensor_custom'])->forceFill([
            'sensor_current' => 60, 'sensor_limit' => null, 'sensor_limit_warn' => null,
            'sensor_limit_low_warn' => null, 'sensor_limit_low' => null,
        ]);
        $discovery = new SensorDiscovery($this->device);
        $discovery->discover($discovered)->sync(sensor_class: 'temperature', poller_type: 'snmp');
        $this->assertEquals(80, $sensor->refresh()->sensor_limit);
        // A late poll still holding the old limits must not write them back.
        record_sensor_data($this->device->attributesToArray(), [$snapshot]);
        $this->assertSame('No', $sensor->refresh()->sensor_custom);
        $this->assertEquals(80, $sensor->sensor_limit);
    }
}
