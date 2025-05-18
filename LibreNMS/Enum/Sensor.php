<?php

namespace LibreNMS\Enum;

use LibreNMS\Traits\EnumToArray;

enum Sensor: string
{
    use EnumToArray;

    case AIRFLOW = 'airflow';
    case BER = 'ber';
    case BITRATE = 'bitrate';
    case CHARGE = 'charge';
    case CHROMATIC_DISPERSION = 'chromatic_dispersion';
    case COOLING = 'cooling';
    case COUNT = 'count';
    case CURRENT = 'current';
    case DBM = 'dbm';
    case DELAY = 'delay';
    case EER = 'eer';
    case FANSPEED = 'fanspeed';
    case FREQUENCY = 'frequency';
    case HUMIDITY = 'humidity';
    case LOAD = 'load';
    case LOSS = 'loss';
    case PERCENT = 'percent';
    case POWER = 'power';
    case POWER_CONSUMED = 'power_consumed';
    case POWER_FACTOR = 'power_factor';
    case PRESSURE = 'pressure';
    case QUALITY_FACTOR = 'quality_factor';
    case RUNTIME = 'runtime';
    case SIGNAL = 'signal';
    case SNR = 'snr';
    case STATE = 'state';
    case TEMPERATURE = 'temperature';
    case TV_SIGNAL = 'tv_signal';
    case VOLTAGE = 'voltage';
    case WATERFLOW = 'waterflow';

    public function unit(): string
    {
        return match ($this) {
            self::AIRFLOW => 'cfm',
            self::BER => 'ratio',
            self::BITRATE => 'bps',
            self::CHARGE => '%',
            self::CHROMATIC_DISPERSION => 'ps/nm',
            self::COOLING => 'W',
            self::COUNT => '#',
            self::CURRENT => 'A',
            self::DBM => 'dBm',
            self::DELAY => 's',
            self::EER => 'eer',
            self::FANSPEED => 'rpm',
            self::FREQUENCY => 'Hz',
            self::HUMIDITY => '%',
            self::LOAD => '%',
            self::LOSS => '%',
            self::PERCENT => '%',
            self::POWER => 'W',
            self::POWER_CONSUMED => 'kWh',
            self::POWER_FACTOR => 'ratio',
            self::PRESSURE => 'kPa',
            self::QUALITY_FACTOR => 'dB',
            self::RUNTIME => 'Min',
            self::SIGNAL => 'dBm',
            self::SNR => 'SNR', // TODO: dB?
            self::STATE => '#',
            self::TEMPERATURE => '°C',
            self::TV_SIGNAL => 'dBmV',
            self::VOLTAGE => 'V',
            self::WATERFLOW => 'l/m',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AIRFLOW => 'angle-double-right',
            self::BER => 'sort-amount-desc',
            self::BITRATE => 'bar-chart',
            self::CHARGE => 'battery-half',
            self::CHROMATIC_DISPERSION => 'indent',
            self::COOLING => 'thermometer-full',
            self::COUNT => 'hashtag',
            self::CURRENT => 'bolt fa-flip-horizontal',
            self::DBM => 'sun-o',
            self::DELAY => 'clock-o',
            self::EER => 'snowflake-o',
            self::FANSPEED => 'refresh',
            self::FREQUENCY => 'line-chart',
            self::HUMIDITY => 'tint',
            self::LOAD => 'percent',
            self::LOSS => 'percentage',
            self::PERCENT => 'percent',
            self::POWER => 'power-off',
            self::POWER_CONSUMED => 'plug',
            self::POWER_FACTOR => 'calculator',
            self::PRESSURE => 'thermometer-empty',
            self::QUALITY_FACTOR => 'arrows',
            self::RUNTIME => 'hourglass-half',
            self::SIGNAL => 'wifi',
            self::SNR => 'signal',
            self::STATE => 'bullseye',
            self::TEMPERATURE => 'thermometer-three-quarters',
            self::TV_SIGNAL => 'signal',
            self::VOLTAGE => 'bolt',
            self::WATERFLOW => 'tint'
        };
    }
}
