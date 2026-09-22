<?php

namespace LibreNMS\Polling\Method;

use App\View\FieldSchema\HasFieldSchema;
use InvalidArgumentException;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;

class PollingMethodRegistry
{
    /**
     * @var array<string, class-string<PollingMethodDefinition>>
     */
    private array $definitions = [];

    /**
     * Register a polling method definition class.
     *
     * @param  class-string<PollingMethodDefinition>  $definitionClass
     */
    public function register(PollingMethodType $type, string $definitionClass): self
    {
        $this->definitions[$type->value] = $definitionClass;

        return $this;
    }

    /**
     * Determine if a polling method is registered.
     */
    public function has(PollingMethodType $type): bool
    {
        return isset($this->definitions[$type->value]);
    }

    /**
     * Get the definition for a polling method.
     */
    public function get(PollingMethodType $type): ?PollingMethodDefinition
    {
        if (! isset($this->definitions[$type->value])) {
            return null;
        }

        return resolve($this->definitions[$type->value]);
    }

    /**
     * Get the definition for a polling method (alias of get).
     */
    public function definition(PollingMethodType $type): ?PollingMethodDefinition
    {
        return $this->get($type);
    }

    /**
     * Get the definition for a polling method, or throw an exception if not found.
     *
     * @throws InvalidArgumentException
     */
    public function require(PollingMethodType $type): PollingMethodDefinition
    {
        $definition = $this->get($type);

        if ($definition === null) {
            throw new InvalidArgumentException("Unknown polling method type: {$type->value}");
        }

        return $definition;
    }

    /**
     * Get the secret definition schema for a polling method, if one is defined.
     */
    public function secretDefinition(PollingMethodType $type): ?HasFieldSchema
    {
        return $this->require($type)->secretDefinition();
    }

    /**
     * Determine if a polling method requires secrets.
     */
    public function hasSecret(PollingMethodType $type): bool
    {
        return $this->secretDefinition($type) !== null;
    }

    /**
     * Get the icon for a polling method.
     */
    public function icon(PollingMethodType $type): string
    {
        return $this->require($type)->icon();
    }

    /**
     * Get the default affects availability value for a polling method.
     */
    public function defaultAffectsAvailability(PollingMethodType $type): bool
    {
        return $this->require($type)->defaultAffectsAvailability();
    }

    /**
     * Get all registered definitions.
     *
     * @return array<string, PollingMethodDefinition>
     */
    public function all(): array
    {
        $resolved = [];
        foreach (array_keys($this->definitions) as $key) {
            $type = PollingMethodType::tryFrom($key);
            if ($type !== null) {
                $resolved[$key] = $this->require($type);
            }
        }

        return $resolved;
    }

    /**
     * Get all registered polling method types.
     *
     * @return array<PollingMethodType>
     */
    public function types(): array
    {
        $types = [];
        foreach (array_keys($this->definitions) as $key) {
            $type = PollingMethodType::tryFrom($key);
            if ($type !== null) {
                $types[] = $type;
            }
        }

        return $types;
    }
}
