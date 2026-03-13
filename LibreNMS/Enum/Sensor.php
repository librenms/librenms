<?php

namespace LibreNMS\Enum;

use LibreNMS\Traits\EnumToArray;

enum Sensor: string
{
    use EnumToArray;

    case Airflow = 'airflow';
    case Ber = 'ber';
    case Bitrate = 'bitrate';
    case Charge = 'charge';
    case ChromaticDispersion = 'chromatic_dispersion';
    case Cooling = 'cooling';
    case Count = 'count';
    case Current = 'current';
    case Dbm = 'dbm';
    case Delay = 'delay';
    case Eer = 'eer';
    case Fanspeed = 'fanspeed';
    case Frequency = 'frequency';
    case Humidity = 'humidity';
    case Load = 'load';
    case Loss = 'loss';
    case Percent = 'percent';
    case Power = 'power';
    case PowerConsumed = 'power_consumed';
    case PowerFactor = 'power_factor';
    case Pressure = 'pressure';
    case QualityFactor = 'quality_factor';
    case Runtime = 'runtime';
    case Signal = 'signal';
    case Snr = 'snr';
    case State = 'state';
    case Temperature = 'temperature';
    case TvSignal = 'tv_signal';
    case Voltage = 'voltage';
    case Waterflow = 'waterflow';
    case SignalLoss = 'signal_loss';

    public function shortLabel(): string
    {
        return __("sensors.$this->value.short");
    }

    public function label(): string
    {
        return __("sensors.$this->value.long");
    }

    public function unit(): string
    {
        return __("sensors.$this->value.unit");
    }

    public function unitLong(): string
    {
        return __("sensors.$this->value.unit_long");
    }

    public function icon(): string
    {
        return match ($this) {
            self::Airflow => 'angle-double-right',
            self::Ber => 'sort-amount-desc',
            self::Bitrate => 'bar-chart',
            self::Charge => 'battery-half',
            self::ChromaticDispersion => 'indent',
            self::Cooling => 'thermometer-full',
            self::Count => 'hashtag',
            self::Current => 'bolt fa-flip-horizontal',
            self::Dbm => 'sun-o',
            self::Delay => 'clock-o',
            self::Eer => 'snowflake-o',
            self::Fanspeed => 'refresh',
            self::Frequency => 'line-chart',
            self::Humidity => 'tint',
            self::Load => 'percent',
            self::Loss => 'percentage',
            self::Percent => 'percent',
            self::Power => 'power-off',
            self::PowerConsumed => 'plug',
            self::PowerFactor => 'calculator',
            self::Pressure => 'thermometer-empty',
            self::QualityFactor => 'arrows',
            self::Runtime => 'hourglass-half',
            self::Signal => 'wifi',
            self::Snr => 'signal',
            self::State => 'bullseye',
            self::Temperature => 'thermometer-three-quarters',
            self::TvSignal => 'signal',
            self::Voltage => 'bolt',
            self::Waterflow => 'tint',
            self::SignalLoss => 'wave-square',
        };
    }
}
