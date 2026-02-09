<?php

namespace LibreNMS\Enum;

use LibreNMS\Traits\EnumToArray;

enum WirelessSensorType: string
{
    use EnumToArray;

    case ApCount = 'ap-count';
    case Clients = 'clients';
    case Quality = 'quality';
    case Capacity = 'capacity';
    case Utilization = 'utilization';
    case Rate = 'rate';
    case Ccq = 'ccq';
    case Snr = 'snr';
    case Sinr = 'sinr';
    case Rsrp = 'rsrp';
    case Rsrq = 'rsrq';
    case Ssr = 'ssr';
    case Mse = 'mse';
    case Xpi = 'xpi';
    case Rssi = 'rssi';
    case Power = 'power';
    case NoiseFloor = 'noise-floor';
    case Errors = 'errors';
    case ErrorRatio = 'error-ratio';
    case ErrorRate = 'error-rate';
    case Frequency = 'frequency';
    case Distance = 'distance';
    case Cell = 'cell';
    case Channel = 'channel';

    public function icon(): string
    {
        return match ($this) {
            self::ApCount => 'wifi',
            self::Clients => 'tablet',
            self::Quality => 'feed',
            self::Capacity => 'feed',
            self::Utilization => 'percent',
            self::Rate => 'tachometer',
            self::Ccq => 'wifi',
            self::Snr => 'signal',
            self::Sinr => 'signal',
            self::Rsrp => 'signal',
            self::Rsrq => 'signal',
            self::Ssr => 'signal',
            self::Mse => 'signal',
            self::Xpi => 'signal',
            self::Rssi => 'signal',
            self::Power => 'bolt',
            self::NoiseFloor => 'signal',
            self::Errors => 'exclamation-triangle',
            self::ErrorRatio => 'exclamation-triangle',
            self::ErrorRate => 'exclamation-triangle',
            self::Frequency => 'line-chart',
            self::Distance => 'space-shuttle',
            self::Cell => 'line-chart',
            self::Channel => 'line-chart',
        };
    }
}
