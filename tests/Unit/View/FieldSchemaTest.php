<?php

namespace LibreNMS\Tests\Unit\View;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;
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

    public function testFormDefaultsOnlyIncludesSelectFields(): void
    {
        $schemaObject = new class implements HasFieldSchema
        {
            use HandlesFieldSchema;

            public function fields(): array
            {
                return [
                    'transport' => FieldDefinition::make('transport', 'select')
                        ->options(['udp' => 'UDP', 'tcp' => 'TCP'])
                        ->default('udp'),
                    'port' => FieldDefinition::make('port', 'number')
                        ->default(161)
                        ->cast('int'),
                    'hostname' => FieldDefinition::make('hostname', 'text'),
                ];
            }
        };

        $formDefaults = $schemaObject->formDefaults();
        $this->assertEquals(['transport' => 'udp'], $formDefaults);
        $this->assertArrayNotHasKey('port', $formDefaults);
        $this->assertArrayNotHasKey('hostname', $formDefaults);
    }

    public function testFilterOverridesOmitsEmptyAndDefaultValues(): void
    {
        $schemaObject = new class implements HasFieldSchema
        {
            use HandlesFieldSchema;

            public function fields(): array
            {
                return [
                    'transport' => FieldDefinition::make('transport', 'select')
                        ->options(['udp' => 'UDP', 'tcp' => 'TCP'])
                        ->default('udp'),
                    'port' => FieldDefinition::make('port', 'number')
                        ->default(161)
                        ->cast('int'),
                    'timeout' => FieldDefinition::make('timeout', 'number')
                        ->default(3)
                        ->cast('int'),
                ];
            }
        };

        // All defaults / empty strings
        $input = [
            'transport' => 'udp',
            'port' => '',
            'timeout' => 3,
        ];
        $this->assertSame([], $schemaObject->filterOverrides($input));

        // Custom override
        $inputWithOverride = [
            'transport' => 'tcp',
            'port' => '162',
            'timeout' => '',
        ];
        $this->assertEquals(['transport' => 'tcp', 'port' => 162], $schemaObject->filterOverrides($inputWithOverride));

        // Clearing an existing override with empty string
        $existing = ['port' => 162];
        $clearingInput = ['port' => ''];
        $this->assertSame([], $schemaObject->filterOverrides($clearingInput, $existing));

        // Partial update preserving existing override
        $partialInput = ['transport' => 'tcp'];
        $this->assertEquals(['transport' => 'tcp', 'port' => 162], $schemaObject->filterOverrides($partialInput, $existing));
    }

    public function testDefinitionsHaveNullableRules(): void
    {
        $snmpRules = (new SnmpPollingMethod)->rules();
        foreach ($snmpRules as $field => $rules) {
            $this->assertContains('nullable', (array) $rules, "SNMP field {$field} should be nullable");
        }

        $ipmiRules = (new IpmiPollingMethod)->rules();
        foreach ($ipmiRules as $field => $rules) {
            $this->assertContains('nullable', (array) $rules, "IPMI field {$field} should be nullable");
        }

        $unixRules = (new UnixAgentPollingMethod)->rules();
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
}
