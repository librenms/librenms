<?php

namespace LibreNMS\Tests\Unit\Discovery;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Discovery\DiscoversSensors;
use LibreNMS\Enum\SensorType;
use LibreNMS\OS;
use LibreNMS\Tests\TestCase;

final class SensorDiscoveryMethodsTest extends TestCase
{
    public function testOnlyMethodsOfTheRequestedClassesRun(): void
    {
        $os = $this->makeOs();
        $os->discoverSensors([SensorType::Temperature]);

        $this->assertSame(['entity', 'mislabeled', 'anything'], $os->called);
    }

    public function testOverrideKeepsTheClassesOfTheOverriddenMethod(): void
    {
        $os = $this->makeOs();
        $os->discoverSensors([SensorType::State]);

        $this->assertSame(['states', 'anything'], $os->called);
    }

    public function testMethodWithoutClassesRunsForAnyClass(): void
    {
        $os = $this->makeOs();
        $os->discoverSensors([SensorType::Airflow]);

        $this->assertSame(['anything'], $os->called);
    }

    public function testClassesMissingFromTheAttributeAreDropped(): void
    {
        Log::shouldReceive('error')->once()->with('discoverMislabeled() returned a voltage sensor, which its DiscoversSensors attribute does not list');
        $os = $this->makeOs();

        $this->assertSame(['temperature-entity', 'dbm-entity', 'temperature-mislabeled', 'humidity-anything'], $this->discovered($os, [SensorType::Temperature, SensorType::Voltage]));
    }

    public function testNothingRunsWithoutClasses(): void
    {
        $os = $this->makeOs();

        $this->assertSame([], $this->discovered($os, []));
        $this->assertSame([], $os->called);
    }

    /**
     * @param  list<SensorType>  $types
     * @return list<string>
     */
    private function discovered(OS $os, array $types): array
    {
        return $os->discoverSensors($types)->map(fn (Sensor $sensor) => "$sensor->sensor_class-$sensor->sensor_type")->values()->all();
    }

    /**
     * @return OS&object{called: list<string>}
     */
    private function makeOs(): OS
    {
        return new class extends OS
        {
            /** @var list<string> */
            public array $called = [];

            public function __construct()
            {
                $device = ['device_id' => 1, 'os' => 'generic'];
                parent::__construct($device);
            }

            public function discoverEntitySensors(): Collection
            {
                $this->called[] = 'entity';

                return new Collection([$this->makeSensor('temperature', 'entity'), $this->makeSensor('dbm', 'entity')]);
            }

            /**
             * @return Collection<int, Sensor>
             */
            #[DiscoversSensors(SensorType::State)]
            protected function discoverStates(): Collection
            {
                $this->called[] = 'states';

                return new Collection([$this->makeSensor('state', 'states')]);
            }

            /**
             * @return Collection<int, Sensor>
             */
            #[DiscoversSensors(SensorType::Temperature)]
            protected function discoverMislabeled(): Collection
            {
                $this->called[] = 'mislabeled';

                return new Collection([$this->makeSensor('temperature', 'mislabeled'), $this->makeSensor('voltage', 'mislabeled')]);
            }

            /**
             * @return Collection<int, Sensor>
             */
            #[DiscoversSensors]
            protected function discoverAnything(): Collection
            {
                $this->called[] = 'anything';

                return new Collection([$this->makeSensor('humidity', 'anything')]);
            }

            protected function discoverLegacySensors(array $types): Collection
            {
                return new Collection;
            }

            public function discoverUpsMibSensors(): Collection
            {
                return new Collection;
            }

            public function discoverYamlSensors(array $types): Collection
            {
                return new Collection;
            }

            private function makeSensor(string $class, string $type): Sensor
            {
                return new Sensor(['sensor_class' => $class, 'sensor_type' => $type, 'sensor_index' => 0]);
            }
        };
    }
}
