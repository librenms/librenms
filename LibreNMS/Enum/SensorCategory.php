<?php

namespace LibreNMS\Enum;

use LibreNMS\Traits\EnumToArray;

/**
 * Keep the category enum in resources/definitions/schema/discovery_schema.json in sync with these cases
 */
enum SensorCategory: string
{
    use EnumToArray;

    case AccessPoint = 'access-point';
    case Cluster = 'cluster';
    case License = 'license';
    case Stack = 'stack';

    public function label(int $count = 1): string
    {
        return trans_choice("sensors.category.$this->value", $count);
    }

    public function icon(): string
    {
        return match ($this) {
            self::AccessPoint => 'wifi',
            self::Cluster => 'sitemap',
            self::License => 'key',
            self::Stack => 'th-large',
        };
    }
}
