<?php

namespace LibreNMS\Enum;

use LibreNMS\Interfaces\PollingMethod;
use LibreNMS\Polling\Method\IcmpPollingMethod;
use LibreNMS\Polling\Method\IpmiPollingMethod;
use LibreNMS\Polling\Method\SnmpPollingMethod;
use LibreNMS\Polling\Method\UnixAgentPollingMethod;
use LibreNMS\Polling\Secrets\IpmiSecretData;
use LibreNMS\Polling\Secrets\SecretData;
use LibreNMS\Polling\Secrets\SnmpSecretData;

enum PollingMethodType: string
{
    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';

    /** @return class-string<PollingMethod> */
    public function methodClass(): string
    {
        return match ($this) {
            self::Icmp => IcmpPollingMethod::class,
            self::Ipmi => IpmiPollingMethod::class,
            self::Snmp => SnmpPollingMethod::class,
            self::UnixAgent => UnixAgentPollingMethod::class,
        };
    }

    /** @return class-string<SecretData>|null */
    public function secretClass(): ?string
    {
        return match ($this) {
            self::Snmp => SnmpSecretData::class,
            self::Ipmi => IpmiSecretData::class,
            default => null,
        };
    }

    public function hasSecret(): bool
    {
        return $this->secretClass() !== null;
    }

    public function icon(): string
    {
        return match ($this) {
            self::Snmp => 'fa-server',
            self::Icmp => 'fa-exchange',
            self::Ipmi => 'fa-microchip',
            self::UnixAgent => 'fa-terminal',
        };
    }

    /**
     * @param  array<string, array>  $schema
     * @return array
     */
    public static function buildSchemaFields(array $schema, string $dataVar = 'formData'): array
    {
        return collect($schema)->map(function (array $field, string $key) use ($dataVar): array {
            $visibleIfExpression = null;

            if (isset($field['visible_if']) && is_array($field['visible_if'])) {
                $visibleIfExpression = collect($field['visible_if'])
                    ->map(function (mixed $condVal, string $condKey): string {
                        if (is_array($condVal) && isset($condVal['$in'])) {
                            return json_encode(array_values($condVal['$in'])) . '.includes(__DATA_VAR__[' . json_encode($condKey) . '])';
                        }

                        return '__DATA_VAR__[' . json_encode($condKey) . '] === ' . json_encode($condVal);
                    })->implode(' && ');

                $visibleIfExpression = str_replace('__DATA_VAR__', $dataVar, $visibleIfExpression);
            }

            return [
                ...$field,
                'key' => $key,
                'field_type' => $field['type'] ?? 'text',
                'visible_if_expression' => $visibleIfExpression,
            ];
        })->values()->all();
    }
}
