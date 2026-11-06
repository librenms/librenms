<?php

namespace App\View;

use App\Models\Secret;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Methods\PollingMethod;

/**
 * The secret part of a polling method form: the types of secret the method can use and their fields.
 */
class PollingMethodSecretForm
{
    /**
     * @param  PollingMethod<PollingMethodConfig>  $method
     * @param  Collection<int, Secret>|null  $secrets  the secrets to pick from, the ones the method can use are included
     * @return array{secret_types: list<array{type: string, label: string, schema_fields: array<int, array<string, mixed>>}>, schema_defaults: array<string, mixed>, available_secrets: Collection<int, Secret>, secret_type_by_id: array<int, string>}
     */
    public static function make(PollingMethod $method, ?Collection $secrets = null, string $dataVar = 'formData'): array
    {
        $secretTypes = $method->secretTypes();
        $available = ($secrets ?? collect())->filter(fn (Secret $secret): bool => $method->usesSecretType($secret->secret_type))->values();

        return [
            'secret_types' => array_map(fn (SecretType $secretType): array => [
                'type' => $secretType->value,
                'label' => $secretType->label(),
                'schema_fields' => $secretType->definition()->buildSchemaFields($dataVar),
            ], $secretTypes),
            // the types have different fields, so the defaults of all of them can be set at once
            'schema_defaults' => array_merge(...array_map(fn (SecretType $secretType): array => $secretType->definition()->schemaDefaults(), $secretTypes)),
            'available_secrets' => $available,
            // the settings shown depend on the type of the selected secret
            'secret_type_by_id' => $available->mapWithKeys(fn (Secret $secret): array => [$secret->id => $secret->secret_type->value])->all(),
        ];
    }
}
