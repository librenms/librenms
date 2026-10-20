<?php

namespace LibreNMS\Enum;

use LibreNMS\Traits\EnumToArray;

/**
 * Keep the category enum in resources/definitions/schema/discovery_schema.json in sync with these cases
 */
enum SensorCategory: string
{
    use EnumToArray;

    case Stack = 'stack';

    public function label(): string
    {
        return __("sensors.category.$this->value");
    }

    public function icon(): string
    {
        return match ($this) {
            self::Stack => 'th-large',
        };
    }
}
