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

    public function unit(): string
    {
        return match ($this) {
            self::Airflow => 'cfm',
            self::Ber => 'ratio',
            self::Bitrate => 'bps',
            self::Charge => '%',
            self::ChromaticDispersion => 'ps/nm',
            self::Cooling => 'W',
            self::Count => '#',
            self::Current => 'A',
            self::Dbm => 'dBm',
            self::Delay => 's',
            self::Eer => 'eer',
            self::Fanspeed => 'rpm',
            self::Frequency => 'Hz',
            self::Humidity => '%',
            self::Load => '%',
            self::Loss => '%',
            self::Percent => '%',
            self::Power => 'W',
            self::PowerConsumed => 'kWh',
            self::PowerFactor => 'ratio',
            self::Pressure => 'kPa',
            self::QualityFactor => 'dB',
            self::Runtime => 'Min',
            self::Signal => 'dBm',
            self::Snr => 'SNR', // TODO: dB?
            self::State => '#',
            self::Temperature => '°C',
            self::TvSignal => 'dBmV',
            self::Voltage => 'V',
            self::Waterflow => 'l/m',
            self::SignalLoss => 'dB',
        };
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
            self::SignalLoss => 'wave-square'
        };
    }
}
