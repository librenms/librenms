<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Sensor;
use App\Models\User;
use App\Models\UserPref;
use LibreNMS\Enum\TemperatureUnit;
use LibreNMS\Tests\TestCase;

class TemperatureUnitTest extends TestCase
{
    public function testDefaultsToCelsiusWithoutUser(): void
    {
        $this->assertSame(TemperatureUnit::Celsius, TemperatureUnit::forUser());
    }

    public function testUserPreference(): void
    {
        $this->assertSame(TemperatureUnit::Fahrenheit, TemperatureUnit::forUser($this->userWithTempUnits('f')));
        $this->assertSame(TemperatureUnit::Celsius, TemperatureUnit::forUser($this->userWithTempUnits('default')));
        $this->assertSame(TemperatureUnit::Celsius, TemperatureUnit::forUser($this->userWithTempUnits(null)));
    }

    public function testFollowsAuthenticatedUser(): void
    {
        $this->actingAs($this->userWithTempUnits('f'));
        $this->assertSame(TemperatureUnit::Fahrenheit, TemperatureUnit::forUser());

        $this->actingAs($this->userWithTempUnits('default'));
        $this->assertSame(TemperatureUnit::Celsius, TemperatureUnit::forUser());
    }

    public function testConvertAndFormat(): void
    {
        $this->assertSame(21.57, TemperatureUnit::Celsius->convert(21.567));
        $this->assertSame(70.0, TemperatureUnit::Fahrenheit->convert(21.11));
        $this->assertSame('21.5 °C', TemperatureUnit::Celsius->format(21.5));
        $this->assertSame('70.7 °F', TemperatureUnit::Fahrenheit->format(21.5));
        $this->assertSame('° Celsius', TemperatureUnit::Celsius->unitLong());
        $this->assertSame('° Fahrenheit', TemperatureUnit::Fahrenheit->unitLong());
    }

    public function testSensorCelsius(): void
    {
        $this->actingAs($this->userWithTempUnits('default'));
        $sensor = new Sensor(['sensor_class' => 'temperature', 'sensor_current' => 21.5]);

        $this->assertSame(TemperatureUnit::Celsius, $sensor->temperatureUnit());
        $this->assertSame('°C', $sensor->unit());
        $this->assertSame('° Celsius', $sensor->unitLong());
        $this->assertSame('21.5 °C', $sensor->formatValue());
        $this->assertSame(21.5, $sensor->convertValue(21.5));
    }

    public function testSensorFahrenheit(): void
    {
        $this->actingAs($this->userWithTempUnits('f'));
        $sensor = new Sensor(['sensor_class' => 'temperature', 'sensor_current' => 21.5]);

        $this->assertSame(TemperatureUnit::Fahrenheit, $sensor->temperatureUnit());
        $this->assertSame('°F', $sensor->unit());
        $this->assertSame('° Fahrenheit', $sensor->unitLong());
        $this->assertSame('70.7 °F', $sensor->formatValue());
        $this->assertSame(70.7, $sensor->convertValue(21.5));
        $this->assertSame(70.7, $sensor->convertValue('21.5'));
        $this->assertNull($sensor->convertValue(null));
    }

    public function testNonTemperatureSensorIgnoresPreference(): void
    {
        $this->actingAs($this->userWithTempUnits('f'));
        $sensor = new Sensor(['sensor_class' => 'voltage', 'sensor_current' => 12]);

        $this->assertNull($sensor->temperatureUnit());
        $this->assertSame('V', $sensor->unit());
        $this->assertSame(12, $sensor->convertValue(12));
    }

    private function userWithTempUnits(?string $value): User
    {
        $user = new User(['username' => 'test']);
        $user->setRelation('preferences', collect($value === null ? [] : [
            new UserPref(['pref' => 'temp_units', 'value' => $value]),
        ]));

        return $user;
    }
}
