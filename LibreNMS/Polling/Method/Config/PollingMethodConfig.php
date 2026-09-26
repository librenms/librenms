<?php

namespace LibreNMS\Polling\Method\Config;

abstract class PollingMethodConfig
{
    public function __construct(
        public bool $enabled = true,
        public bool $affectsAvailability = true,
    ) {
    }

    abstract public function isValid(): bool;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    abstract public static function default(): static;

    /**
     * Array representation of non-secret settings.
     *
     * @return array<string, mixed>
     */
    public function settingsArray(): array
    {
        return [];
    }

    /**
     * Diffs current settings against default settings to produce minimal DB storage payload.
     *
     * @return array<string, mixed>
     */
    public function toSparseSettings(): array
    {
        $default = static::default()->settingsArray();
        $sparse = [];

        foreach ($this->settingsArray() as $key => $value) {
            $defaultVal = $default[$key] ?? null;
            if ($value !== $defaultVal && $value !== null && $value !== '') {
                $sparse[$key] = $value;
            }
        }

        return $sparse;
    }
}
