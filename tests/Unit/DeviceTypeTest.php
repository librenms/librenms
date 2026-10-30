<?php

namespace LibreNMS\Tests\Unit;

use Illuminate\Support\Facades\Lang;
use LibreNMS\Enum\DeviceType;
use LibreNMS\Tests\TestCase;

final class DeviceTypeTest extends TestCase
{
    public function testEnumMatchesOsSchema(): void
    {
        $schema = json_decode(file_get_contents(resource_path('definitions/schema/os_schema.json')), true);

        $this->assertEqualsCanonicalizing(
            array_column(DeviceType::cases(), 'value'),
            $schema['properties']['type']['enum'],
            'DeviceType enum and os_schema.json type enum differ'
        );
    }

    public function testEveryTypeHasDefaultMenuEntry(): void
    {
        $definitions = json_decode(file_get_contents(resource_path('definitions/config_definitions.json')), true);

        $this->assertEqualsCanonicalizing(
            array_column(DeviceType::cases(), 'value'),
            array_column($definitions['config']['device_types']['default'], 'type'),
            'DeviceType enum and device_types config default differ'
        );
    }

    public function testEveryTypeHasTranslation(): void
    {
        foreach (DeviceType::cases() as $type) {
            $this->assertTrue(Lang::has('device.types.' . $type->value, 'en', false), "Missing translation device.types.$type->value");
        }
    }
}
