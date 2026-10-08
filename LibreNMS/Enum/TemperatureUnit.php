<?php

namespace LibreNMS\Enum;

use App\Models\User;
use App\Models\UserPref;
use LibreNMS\Util\Rewrite;

enum TemperatureUnit
{
    case Celsius;
    case Fahrenheit;

    /**
     * Temperature unit preferred by the given user, or the logged-in user if none given.
     */
    public static function forUser(?User $user = null): self
    {
        /** @var ?User $user */
        $user ??= auth()->user();

        return $user && UserPref::getPref($user, 'temp_units') == 'f' ? self::Fahrenheit : self::Celsius;
    }

    public function unit(): string
    {
        return match ($this) {
            self::Celsius => __('sensors.temperature.unit'),
            self::Fahrenheit => __('sensors.temperature.unit_f'),
        };
    }

    public function unitLong(): string
    {
        return match ($this) {
            self::Celsius => __('sensors.temperature.unit_long'),
            self::Fahrenheit => __('sensors.temperature.unit_long_f'),
        };
    }

    /**
     * Convert a value in Celsius to this unit.
     */
    public function convert(float $celsius): float
    {
        return match ($this) {
            self::Celsius => round($celsius, 2),
            self::Fahrenheit => Rewrite::celsiusToFahrenheit($celsius),
        };
    }

    /**
     * Convert a value in Celsius to this unit and format it with the unit suffix.
     */
    public function format(float $celsius): string
    {
        return $this->convert($celsius) . ' ' . $this->unit();
    }
}
