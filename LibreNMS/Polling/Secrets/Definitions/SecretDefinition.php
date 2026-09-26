<?php

namespace LibreNMS\Polling\Secrets\Definitions;

use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Enum\SecretType;

abstract class SecretDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    public static function for(?SecretType $type): ?self
    {
        return match ($type) {
            SecretType::Snmp => resolve(SnmpSecretDefinition::class),
            SecretType::Ipmi => resolve(IpmiSecretDefinition::class),
            null => null,
        };
    }

    /**
     * Initial values for a new secret.
     *
     * @return array<string, mixed>
     */
    public function schemaDefaults(): array
    {
        return collect($this->fields())
            ->map(fn (FieldDefinition $field): mixed => $field->getDefault())
            ->filter(fn (mixed $v): bool => $v !== null)
            ->all();
    }
}
