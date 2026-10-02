<?php

namespace LibreNMS\Tests\Unit\Polling;

use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Definitions\IpmiDefinition;
use LibreNMS\Polling\Method\Definitions\SnmpDefinition;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Tests\TestCase;

final class PollingMethodDefinitionTest extends TestCase
{
    public function testFilterOverridesKeepsOnlySetValues(): void
    {
        $definition = new SnmpDefinition;

        // empty means default, set values are kept even when they match the default
        $this->assertSame(
            ['transport' => 'udp', 'port' => 1161],
            $definition->filterOverrides(['transport' => 'udp', 'port' => '1161', 'timeout' => '', 'retries' => null, 'unknown' => 'x'])
        );
        $this->assertSame([], $definition->filterOverrides([]));
    }

    public function testFilterOverridesCastsValues(): void
    {
        $this->assertSame(
            ['timeout' => 2.5, 'retries' => 3, 'bulk' => false, 'context' => 'vrf-a'],
            (new SnmpDefinition)->filterOverrides(['timeout' => '2.5', 'retries' => '3', 'bulk' => '0', 'context' => 'vrf-a'])
        );
        $this->assertSame(
            ['ciphersuite' => 3, 'type' => 'lanplus'],
            (new IpmiDefinition)->filterOverrides(['ciphersuite' => '3', 'type' => 'lanplus'])
        );
    }

    public function testFilterOverridesDropsValuesThatCanNotBeUsed(): void
    {
        $this->assertSame([], (new SnmpDefinition)->filterOverrides([
            'max_oid' => '0', // below min
            'port' => '70000', // above max
            'bulk' => 'maybe', // not a boolean
        ]));
    }

    public function testSettingsFieldsShowDefaults(): void
    {
        $fields = collect((new SnmpDefinition)->settingsFields(['transport' => 'tcp', 'port' => 1161, 'max_repeaters' => 7] + (new SnmpPollingMethod)->defaults()))->keyBy('key');

        // selects get an empty "Default" option instead of preselecting a value
        $this->assertSame('Default (TCP)', $fields['transport']['default_option']);
        $this->assertArrayNotHasKey('default', $fields['transport']);
        $this->assertSame('Default (Yes)', $fields['bulk']['default_option']);

        // other fields show the default as a placeholder
        $this->assertSame('1161', $fields['port']['placeholder']);
        $this->assertSame('7', $fields['max_repeaters']['placeholder']);
        $this->assertArrayNotHasKey('placeholder', $fields['context']);

        // explicit placeholders are kept
        $ipmiFields = collect((new IpmiDefinition)->settingsFields((new IpmiPollingMethod)->defaults()))->keyBy('key');
        $this->assertSame("Default: device's hostname", $ipmiFields['hostname']['placeholder']);
        $this->assertSame('Default: ipmitool', $ipmiFields['timeout']['placeholder']); // ipmitool's default differs by interface
        $this->assertSame('Auto-detect', $ipmiFields['type']['default_option']);
    }

    public function testEverySettingIsOptional(): void
    {
        foreach (PollingMethodType::cases() as $type) {
            foreach ((new PollingMethodRegistry)->get($type)->definition()->rules() as $field => $rules) {
                $this->assertContains('nullable', (array) $rules, "{$type->value} setting $field should be nullable");
            }
        }
    }
}
