<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\View\FieldSchema\FieldDefinition;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Modules\Core;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;
use SnmpQuery;

/**
 * @extends PollingMethodDefinition<SnmpConfig>
 */
final class SnmpPollingMethodDefinition extends PollingMethodDefinition
{
    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'transport' => FieldDefinition::make('transport', 'select')
                ->options([
                    'udp' => 'UDP',
                    'tcp' => 'TCP',
                    'udp6' => 'UDP6',
                    'tcp6' => 'TCP6',
                ])
                ->default(fn () => LibrenmsConfig::get('snmp.transports.0', 'udp'))
                ->rules(['nullable', 'string', 'in:udp,tcp,udp6,tcp6']),

            'port' => FieldDefinition::make('port', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('snmp.port', 161))
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535'])
                ->cast('int'),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->default(fn () => (float) LibrenmsConfig::get('snmp.timeout', 1))
                ->min(0.1)
                ->max(60)
                ->rules(['nullable', 'numeric', 'min:0.1', 'max:60'])
                ->cast('float'),

            'retries' => FieldDefinition::make('retries', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('snmp.retries', 5))
                ->min(0)
                ->max(10)
                ->rules(['nullable', 'integer', 'min:0', 'max:10'])
                ->cast('int'),

            'max_repeaters' => FieldDefinition::make('max_repeaters', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('snmp.max_repeaters', 10))
                ->min(0)
                ->max(30)
                ->rules(['nullable', 'integer', 'min:0', 'max:30'])
                ->cast('int'),

            'max_oid' => FieldDefinition::make('max_oid', 'number')
                ->default(fn () => max(1, (int) LibrenmsConfig::get('snmp.max_oid', 10)))
                ->min(1)
                ->max(100)
                ->rules(['nullable', 'integer', 'min:1', 'max:100'])
                ->cast('int'),

            'port_association_mode' => FieldDefinition::make('port_association_mode', 'select')
                ->options(array_combine(PortAssociationMode::getModes(), PortAssociationMode::getModes()))
                ->default(fn () => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'))
                ->rules(['nullable', 'string', Rule::in(PortAssociationMode::getModes())]),
        ];
    }

    public function fallbackConfig(\App\Models\Device $device): SnmpConfig
    {
        $method = $device->pollingMethod(\LibreNMS\Enum\PollingMethodType::Snmp);
        if ($method && $method->secret !== null) {
            /** @var SnmpConfig */
            return $method->toConfig();
        }

        if ($device->exists) {
            \App\Models\Eventlog::log('Missing SNMP polling method or credentials, falling back to legacy device fields.', $device, 'snmp', \LibreNMS\Enum\Severity::Error);
        }

        return SnmpConfig::fromLegacyDeviceFields($device);
    }

    /**
     * @inheritDoc
     */
    public function discover(\App\Models\Device $device, \App\Models\DevicePollingMethod $method): \LibreNMS\Polling\Method\Probe\ProbeResult
    {
        $testDevice = clone $device;

        // If a specific secret was supplied on the method, test that directly
        if ($method->relationLoaded('secret') && $method->secret !== null) {
            $testDevice->setRelation('pollingMethods', collect([$method]));
            $result = $this->probe()->check($testDevice);
            if ($result->isSuccess()) {
                return $result;
            }

            $secret = $method->secret;
            $secretData = $secret->toSecretData();
            $reasons = [];
            if ($secretData instanceof \LibreNMS\Polling\Secrets\Data\SnmpSecretData) {
                $target = $secret->description ?: ($secretData->community ?? ($secretData->authname ?? 'custom'));
                $reasons[$secretData->version] = (string) $target;
            }

            return \LibreNMS\Polling\Method\Probe\ProbeResult::failure(
                array_merge($result->stats(), ['reasons' => $reasons]),
                $result->errorMessage()
            );
        }

        // Otherwise, attempt ordered default credentials
        /** @var array<int, int> $defaultSecretIds */
        $defaultSecretIds = (array) LibrenmsConfig::get('snmp.default_credentials', []);
        $defaultSecrets = \App\Models\Secret::where('secret_type', \LibreNMS\Enum\SecretType::Snmp)
            ->whereIn('id', $defaultSecretIds)
            ->get()
            ->sortBy(fn ($s) => array_search($s->id, $defaultSecretIds));

        $reasons = [];
        $lastResult = null;
        foreach ($defaultSecrets as $secret) {
            $method->setRelation('secret', $secret);
            $method->secret_id = $secret->id;
            $testDevice->setRelation('pollingMethods', collect([$method]));

            $result = $this->probe()->check($testDevice);
            if ($result->isSuccess()) {
                return $result;
            }

            $lastResult = $result;
            $secretData = $secret->toSecretData();
            if ($secretData instanceof \LibreNMS\Polling\Secrets\Data\SnmpSecretData) {
                $reasons[$secretData->version] = $secret->description;
            }
        }

        return \LibreNMS\Polling\Method\Probe\ProbeResult::failure(
            array_merge($lastResult ? $lastResult->stats() : [], ['reasons' => $reasons]),
            $lastResult?->errorMessage()
        );
    }

    public function enrichDeviceMetadata(Device $device): void
    {
        $sysName = SnmpQuery::device($device)->get('SNMPv2-MIB::sysName.0')->value();
        if (! empty($sysName)) {
            $device->sysName = (string) $sysName;
        }

        $device->os = Core::detectOS($device);
    }

    public function defaultAffectsAvailability(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-server';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return SnmpConfig::class;
    }

    /**
     * @inheritDoc
     */
    public function probe(): \LibreNMS\Polling\Method\Probe\SnmpProbe
    {
        return new \LibreNMS\Polling\Method\Probe\SnmpProbe();
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): SnmpSecretDefinition
    {
        return new SnmpSecretDefinition;
    }
}
