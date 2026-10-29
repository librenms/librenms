<?php

namespace LibreNMS\Polling\Secrets\Definitions;

/**
 * No known fields, so every value is treated as sensitive.
 */
class UnknownSecretDefinition extends SecretDefinition
{
    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mask(array $data): array
    {
        return array_map(fn (mixed $value): mixed => $value === null || $value === '' ? $value : self::MASK, $data);
    }

    /**
     * @inheritDoc
     */
    public function unmask(array $data, array $original): array
    {
        foreach ($data as $key => $value) {
            if ($value === self::MASK) {
                $data[$key] = $original[$key] ?? null;
            }
        }

        return $data;
    }
}
