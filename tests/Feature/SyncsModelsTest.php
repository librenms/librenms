<?php

namespace LibreNMS\Tests\Feature;

use App\Discovery\Sensor as SensorDiscovery;
use App\Models\Device;
use App\Models\Mempool;
use App\Models\Sensor;
use App\Models\Storage;
use App\Models\WirelessSensor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SyncsModelsTest extends TestCase
{
    use DatabaseTransactions;

    private const DISCOVERED_LIMITS = [
        'sensor_limit' => 70,
        'sensor_limit_warn' => 60,
        'sensor_limit_low_warn' => 3,
        'sensor_limit_low' => 2,
    ];

    /**
     * @return array<string, array{class-string<Mempool|Storage>, string, string, string}>
     */
    public static function warnThresholdModels(): array
    {
        return [
            'storage' => [Storage::class, 'storage', 'storage_descr', 'storage_perc_warn'],
            'mempool' => [Mempool::class, 'mempools', 'mempool_descr', 'mempool_perc_warn'],
        ];
    }

    /**
     * @param  class-string<Mempool|Storage>  $modelClass
     */
    #[DataProvider('warnThresholdModels')]
    public function testSyncKeepsWarnThresholdOnExistingModels(string $modelClass, string $relationship, string $descrField, string $warnField): void
    {
        $device = Device::factory()->create();
        $existing = $modelClass::factory()->for($device)->createQuietly([$warnField => null]);

        $this->syncModels($device, $relationship, $this->discovered($existing, [$descrField => 'renamed', $warnField => 80]));

        $existing->refresh();
        $this->assertSame('renamed', $existing->$descrField);
        $this->assertNull($existing->$warnField);
    }

    /**
     * @param  class-string<Mempool|Storage>  $modelClass
     */
    #[DataProvider('warnThresholdModels')]
    public function testSyncSetsWarnThresholdOnNewModels(string $modelClass, string $relationship, string $descrField, string $warnField): void
    {
        $device = Device::factory()->create();

        $this->syncModels($device, $relationship, $modelClass::factory()->make([$warnField => 42]));

        $this->assertSame(42, $device->$relationship()->first()->$warnField);
    }

    /**
     * @param  class-string<Mempool|Storage>  $modelClass
     */
    #[DataProvider('warnThresholdModels')]
    public function testFillNewKeepsWarnThreshold(string $modelClass, string $relationship, string $descrField, string $warnField): void
    {
        $device = Device::factory()->create();
        $existing = $modelClass::factory()->for($device)->createQuietly([$warnField => 60]);

        $filled = $this->fillNew($existing, $this->discovered($existing, [$descrField => 'renamed', $warnField => 80]));

        $this->assertSame('renamed', $filled->$descrField);
        $this->assertSame(60, $filled->$warnField);
    }

    public function testSensorDiscoveryKeepsCustomLimits(): void
    {
        $device = Device::factory()->create();
        $existing = $this->existingSensor(Sensor::class, $device, ['sensor_custom' => 'Yes', 'sensor_limit' => 50]);

        $this->discoverSensor($existing, ['sensor_descr' => 'renamed'] + self::DISCOVERED_LIMITS);

        $existing->refresh();
        $this->assertSame('renamed', $existing->sensor_descr);
        $this->assertSame('Yes', $existing->sensor_custom);
        $this->assertEquals(50, $existing->sensor_limit);
        $this->assertNull($existing->sensor_limit_warn);
    }

    public function testSensorDiscoveryUpdatesLimits(): void
    {
        $device = Device::factory()->create();
        $existing = $this->existingSensor(Sensor::class, $device, ['sensor_limit' => 50, 'sensor_limit_warn' => 40]);

        $this->discoverSensor($existing, ['sensor_limit' => 70, 'sensor_limit_warn' => null]);

        $existing->refresh();
        $this->assertEquals(70, $existing->sensor_limit);
        $this->assertEquals(40, $existing->sensor_limit_warn, 'Limits not provided by discovery are kept');
    }

    public function testSensorDiscoveryAppliesLimitsAfterReset(): void
    {
        $device = Device::factory()->create();
        $existing = $this->existingSensor(Sensor::class, $device, ['sensor_custom' => 'Reset', 'sensor_limit' => 50]);

        $this->discoverSensor($existing, self::DISCOVERED_LIMITS);

        $existing->refresh();
        $this->assertSame('No', $existing->sensor_custom);
        $this->assertEquals(70, $existing->sensor_limit);
    }

    public function testSyncKeepsCustomWirelessSensorLimits(): void
    {
        $device = Device::factory()->create();
        $existing = $this->existingSensor(WirelessSensor::class, $device, ['sensor_custom' => 'Yes', 'sensor_limit' => 50]);

        $this->syncModels($device, 'wirelessSensors', $this->discovered($existing, ['sensor_descr' => 'renamed'] + self::DISCOVERED_LIMITS));

        $existing->refresh();
        $this->assertSame('renamed', $existing->sensor_descr);
        $this->assertSame('Yes', $existing->sensor_custom);
        $this->assertEquals(50, $existing->sensor_limit);
        $this->assertNull($existing->sensor_limit_warn);
    }

    /**
     * Removing custom limits clears them, so discovery can set them again
     */
    public function testSyncOnlyFillsEmptyWirelessSensorLimits(): void
    {
        $device = Device::factory()->create();
        $existing = $this->existingSensor(WirelessSensor::class, $device, ['sensor_limit' => 50]);

        $this->syncModels($device, 'wirelessSensors', $this->discovered($existing, self::DISCOVERED_LIMITS));

        $existing->refresh();
        $this->assertEquals(50, $existing->sensor_limit);
        $this->assertEquals(60, $existing->sensor_limit_warn);
        $this->assertEquals(3, $existing->sensor_limit_low_warn);
        $this->assertEquals(2, $existing->sensor_limit_low);
    }

    /**
     * @template T of Sensor|WirelessSensor
     *
     * @param  class-string<T>  $modelClass
     * @param  array<string, mixed>  $attributes
     * @return T
     */
    private function existingSensor(string $modelClass, Device $device, array $attributes): Sensor|WirelessSensor
    {
        $sensor = $modelClass::factory()->for($device)->createQuietly($attributes + [
            'sensor_class' => $modelClass === Sensor::class ? 'temperature' : 'clients',
            'sensor_custom' => 'No',
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low_warn' => null,
            'sensor_limit_low' => null,
        ]);

        // load database defaults that are part of the composite key
        return $sensor->refresh();
    }

    /**
     * Build the model discovery would return for an existing model
     *
     * @param  array<string, mixed>  $attributes
     */
    private function discovered(Model $existing, array $attributes): Model
    {
        // discovery does not set the custom flag
        return $existing->replicate(['sensor_custom'])->forceFill($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function discoverSensor(Sensor $existing, array $attributes): void
    {
        $discovered = $existing->replicate(['sensor_custom'])->forceFill($attributes);

        (new SensorDiscovery($existing->device))
            ->discover($discovered)
            ->sync(sensor_class: $discovered->sensor_class, poller_type: $discovered->poller_type);
    }

    private function syncModels(Device $device, string $relationship, Model $discovered): void
    {
        $this->modelSyncer()->syncModels($device, $relationship, new Collection([$discovered]));
    }

    private function fillNew(Model $existing, Model $discovered): Model
    {
        return $this->modelSyncer()->fillNew(new Collection([$existing]), new Collection([$discovered]))->first();
    }

    private function modelSyncer(): object
    {
        return new class
        {
            use SyncsModels {
                syncModels as public;
                fillNew as public;
            }
        };
    }
}
