<?php

namespace LibreNMS\Polling\Method\Config;

use Illuminate\Support\Str;

/**
 * Typed settings for a polling method.
 * Setting keys are snake_case versions of the property names (max_oid => maxOid).
 */
abstract class PollingMethodConfig
{
    public function __construct(
        public bool $enabled = true,
        public bool $affectsAvailability = true,
    ) {
    }

    /**
     * Settings for a newly added polling method.
     * This is the single source of default values for the method.
     */
    abstract public static function default(): static;

    /**
     * Set properties from settings or secret data, values must already be cast to the property type.
     *
     * @param  array<string, mixed>  $values
     */
    public function fill(array $values): static
    {
        foreach ($values as $key => $value) {
            $property = Str::camel($key);
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }

        return $this;
    }

    /**
     * Get the value of a setting by its key.
     */
    public function setting(string $key): mixed
    {
        $property = Str::camel($key);

        return property_exists($this, $property) ? $this->$property : null;
    }
}
