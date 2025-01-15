<?php

namespace LibreNMS\Enum;

use Illuminate\Support\Collection;
use ReflectionClass;

class SensorClass {
    const airflow = 'angle-double-right';
    const ber = 'sort-amount-desc';
    const charge = 'battery-half';
    const chromatic_dispersion = 'indent';
    const cooling = 'thermometer-full';
    const count = 'hashtag';
    const current = 'bolt fa-flip-horizontal';
    const dbm = 'sun-o';
    const delay = 'clock-o';
    const eer = 'snowflake-o';
    const fanspeed = 'refresh';
    const frequency = 'line-chart';
    const humidity = 'tint';
    const load = 'percent';
    const loss = 'percentage';
    const power = 'power-off';
    const power_consumed = 'plug';
    const power_factor = 'calculator';
    const pressur = 'thermometer-empty';
    const quality_factor = 'arrows';
    const runtime = 'hourglass-half';
    const signal = 'wifi';
    const snr = 'signal';
    const state = 'bullseye';
    const temperature = 'thermometer-three-quarters';
    const tv_signal = 'signal';
    const bitrate = 'bar-chart';
    const voltage = 'bolt';
    const waterflow = 'tint';
    const percent = 'percent';


    public static function all(): Collection
    {
        return static::iconMap()->keys();
    }

    public static function iconMap(): Collection
    {
        return collect((new ReflectionClass(__CLASS__))->getConstants());
    }

    public static function descrMap(): Collection
    {
        return self::all()->mapWithKeys(fn ($type) => [$type => self::descr($type)]);
    }

    public static function descrLongMap(): Collection
    {
        return self::all()->mapWithKeys(fn ($type) => [$type => self::descrLong($type)]);
    }

    public static function unitMap(): Collection
    {
        return self::all()->mapWithKeys(fn ($type) => [$type => self::unit($type)]);
    }

    public static function unitLongMap(): Collection
    {
        return self::all()->mapWithKeys(fn ($type) => [$type => self::unitLong($type)]);
    }

    public static function icon(string $class): string
    {
        return self::iconMap()->get($class, 'delicius');
    }

    public static function descr(string $class): string
    {
        return __('sensors.' . $class . '.short');
    }

    public static function descrLong(string $class): string
    {
        return __('sensors.' . $class . '.long');
    }

    public static function unit(string $class): string
    {
        return __('sensors.' . $class . '.unit');
    }

    public static function unitLong(string $class): string
    {
        return __('sensors.' . $class . '.unit_long');
    }
}
