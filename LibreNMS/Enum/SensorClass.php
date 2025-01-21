<?php

namespace LibreNMS\Enum;

enum SensorClass
{
    case airflow;
    case ber;
    case charge;
    case chromatic_dispersion;
    case cooling;
    case count;
    case current;
    case dbm;
    case delay;
    case eer;
    case fanspeed;
    case frequency;
    case humidity;
    case load;
    case loss;
    case power;
    case power_consumed;
    case power_factor;
    case pressur;
    case quality_factor;
    case runtime;
    case signal;
    case snr;
    case state;
    case temperature;
    case tv_signal;
    case bitrate;
    case voltage;
    case waterflow;
    case percent;

    public function icon(): string
    {
        return match($this){
            self::airflow => 'angle-double-right',
            self::ber => 'sort-amount-desc',
            self::charge => 'battery-half',
            self::chromatic_dispersion => 'indent',
            self::cooling => 'thermometer-full',
            self::count => 'hashtag',
            self::current => 'bolt fa-flip-horizontal',
            self::dbm => 'sun-o',
            self::delay => 'clock-o',
            self::eer => 'snowflake-o',
            self::fanspeed => 'refresh',
            self::frequency => 'line-chart',
            self::humidity => 'tint',
            self::load => 'percent',
            self::loss => 'percentage',
            self::power => 'power-off',
            self::power_consumed => 'plug',
            self::power_factor => 'calculator',
            self::pressur => 'thermometer-empty',
            self::quality_factor => 'arrows',
            self::runtime => 'hourglass-half',
            self::signal => 'wifi',
            self::snr => 'signal',
            self::state => 'bullseye',
            self::temperature => 'thermometer-three-quarters',
            self::tv_signal => 'signal',
            self::bitrate => 'bar-chart',
            self::voltage => 'bolt',
            self::waterflow => 'tint',
            self::percent => 'percent',
        };
    }

    public function descr(): string
    {
        return __('sensors.' . $this->name . '.short');
    }

    public function descrLong(): string
    {
        return __('sensors.' . $this->name . '.long');
    }

    public function unit(): string
    {
        return __('sensors.' . $this->name . '.unit');
    }

    public function unitLong(): string
    {
        return __('sensors.' . $this->name. '.unit_long');
    }
}
