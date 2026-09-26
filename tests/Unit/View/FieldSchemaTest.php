<?php

namespace LibreNMS\Tests\Unit\View;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\IpmiConfig;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Method\Definitions\IpmiDefinition;
use LibreNMS\Polling\Method\Definitions\SnmpDefinition;
use LibreNMS\Polling\Method\Definitions\UnixAgentDefinition;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Tests\TestCase;

final class FieldSchemaTest extends TestCase
{
    public function testFieldDefinitionDefaultAndPlaceholder(): void
    {
        $fieldWithDefault = FieldDefinition::make('port', 'number')
            ->default(161)
            ->cast('int');
        $this->assertSame(161, $fieldWithDefault->getDefault());
        $this->assertSame('161', $fieldWithDefault->getPlaceholder());

        $fieldWithCallableDefault = FieldDefinition::make('timeout', 'number')
            ->default(fn (): int => 5)
            ->cast('int');
        $this->assertSame(5, $fieldWithCallableDefault->getDefault());
        $this->assertSame('5', $fieldWithCallableDefault->getPlaceholder());

        $fieldWithExplicitPlaceholder = FieldDefinition::make('hostname', 'text')
            ->placeholder('device hostname');
        $this->assertNull($fieldWithExplicitPlaceholder->getDefault());
        $this->assertSame('device hostname', $fieldWithExplicitPlaceholder->getPlaceholder());

        $fieldRules = FieldDefinition::make('retries', 'number')
            ->min(1)
            ->max(10);
        $this->assertSame(['nullable', 'integer', 'min:1', 'max:10'], $fieldRules->getRules());
    }

    public function testFilterOverridesKeepsOnlyUserSetValues(): void
    {
        $definition = new SnmpDefinition();

        // empty means default, set values are kept even when they match the default
        $this->assertSame(
            ['transport' => 'udp', 'port' => 1161],
            $definition->filterOverrides(['transport' => 'udp', 'port' => '1161', 'timeout' => '', 'retries' => null, 'unknown' => 'x'])
        );
        $this->assertSame([], $definition->filterOverrides([]));
    }

    public function testSettingsFieldsShowDefaults(): void
    {
        $fields = collect((new SnmpDefinition)->settingsFields(new SnmpConfig(transport: 'tcp', port: 1161, maxRepeaters: 7)))->keyBy('key');

        // selects get an empty "Default" option instead of preselecting a value
        $this->assertSame('Default (TCP)', $fields['transport']['default_option']);
        $this->assertArrayNotHasKey('default', $fields['transport']);

        // other fields show the default as a placeholder
        $this->assertSame('1161', $fields['port']['placeholder']);
        $this->assertSame('7', $fields['max_repeaters']['placeholder']);
        $this->assertArrayNotHasKey('placeholder', $fields['context']);

        // explicit placeholders are kept
        $ipmiFields = collect((new IpmiDefinition)->settingsFields(IpmiConfig::default()))->keyBy('key');
        $this->assertSame("Default: device's hostname", $ipmiFields['hostname']['placeholder']);
    }

    public function testSnmpDefaultsFollowDeviceOs(): void
    {
        LibrenmsConfig::set('os.test-os.snmp.max_repeaters', 3);
        $method = new SnmpPollingMethod();

        $this->assertSame(3, $method->defaultConfig(new Device(['os' => 'test-os']))->maxRepeaters);

        // nothing stored, so the OS default is used rather than pinned
        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Snmp, 'settings' => []]);
        $deviceMethod->setRelation('device', new Device(['os' => 'test-os']));
        $this->assertSame(3, $method->config($deviceMethod)->maxRepeaters);
    }

    public function testDefinitionsHaveNullableRules(): void
    {
        $snmpRules = (new SnmpDefinition)->rules();
        foreach ($snmpRules as $field => $rules) {
            $this->assertContains('nullable', (array) $rules, "SNMP field {$field} should be nullable");
        }

        $ipmiRules = (new IpmiDefinition)->rules();
        foreach ($ipmiRules as $field => $rules) {
            $this->assertContains('nullable', (array) $rules, "IPMI field {$field} should be nullable");
        }

        $unixRules = (new UnixAgentDefinition)->rules();
        foreach ($unixRules as $field => $rules) {
            $this->assertContains('nullable', (array) $rules, "Unix agent field {$field} should be nullable");
        }
    }

    public function testIpmiConfigFallsBackToDeviceHostname(): void
    {
        $device = new Device(['hostname' => 'switch.example.com']);
        $devicePollingMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Ipmi,
            'settings' => [],
        ]);
        $devicePollingMethod->setRelation('device', $device);

        $ipmiMethod = new IpmiPollingMethod();
        $config = $ipmiMethod->config($devicePollingMethod);
        $this->assertSame('switch.example.com', $config->hostname);
        $this->assertSame(623, $config->port);
        $this->assertSame(3, $config->timeout);

        // With explicit hostname override
        $overrideMethod = new DevicePollingMethod([
            'method_type' => PollingMethodType::Ipmi,
            'settings' => ['hostname' => 'ipmi.example.com', 'port' => 6230],
        ]);
        $overrideMethod->setRelation('device', $device);
        $overrideConfig = $ipmiMethod->config($overrideMethod);
        $this->assertSame('ipmi.example.com', $overrideConfig->hostname);
        $this->assertSame(6230, $overrideConfig->port);
    }

    public function testPollingMethodConfigDefaults(): void
    {
        $snmpConfig = SnmpConfig::default();
        $this->assertSame('udp', $snmpConfig->transport);
        $this->assertSame(161, $snmpConfig->port);
        $this->assertSame(1.0, (float) $snmpConfig->timeout);
        $this->assertSame(5, $snmpConfig->retries);

        $customSnmp = SnmpConfig::fromSettings(['transport' => 'tcp', 'port' => 1161, 'context' => 'vrf-a']);
        $this->assertSame('tcp', $customSnmp->transport);
        $this->assertSame(1161, $customSnmp->port);
        $this->assertSame('vrf-a', $customSnmp->context);

        $unixConfig = \LibreNMS\Polling\Method\Config\UnixAgentConfig::default();
        $this->assertSame(6556, $unixConfig->port);
        $this->assertSame(10, $unixConfig->timeout);
        $this->assertSame(6557, \LibreNMS\Polling\Method\Config\UnixAgentConfig::fromSettings(['port' => 6557])->port);

        $this->assertSame('default', \LibreNMS\Polling\Method\Config\IcmpConfig::default()->ipVersion);
        $this->assertSame('ipv6', \LibreNMS\Polling\Method\Config\IcmpConfig::fromSettings(['ip_version' => 'ipv6'])->ipVersion);
    }
}
