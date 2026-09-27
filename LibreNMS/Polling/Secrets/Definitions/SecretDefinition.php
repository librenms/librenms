<?php

namespace LibreNMS\Polling\Secrets\Definitions;

use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Secrets\Data\SecretData;

abstract class SecretDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    /**
     * Placeholder sent to the browser in place of a secret value the user may not see.
     */
    public const MASK = '********';

    /**
     * Typed secret data from the decrypted secret.
     *
     * @param  array<string, mixed>  $data
     */
    abstract public function data(array $data): SecretData;

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

    /**
     * Replace the values of sensitive fields with the mask.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function mask(array $data): array
    {
        foreach ($this->sensitiveKeys() as $key) {
            if (! empty($data[$key])) {
                $data[$key] = self::MASK;
            }
        }

        return $data;
    }

    /**
     * Restore sensitive values that were sent back masked (unchanged) from the original data.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $original
     * @return array<string, mixed>
     */
    public function unmask(array $data, array $original): array
    {
        foreach ($this->sensitiveKeys() as $key) {
            if (($data[$key] ?? null) === self::MASK) {
                $data[$key] = $original[$key] ?? null;
            }
        }

        return $data;
    }

    /**
     * @return string[]
     */
    private function sensitiveKeys(): array
    {
        return collect($this->fields())
            ->filter(fn (FieldDefinition $field): bool => $field->type === 'password')
            ->keys()
            ->all();
    }
}
