<?php

namespace LibreNMS\Polling\Method;

use InvalidArgumentException;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;

class PollingMethodRegistry
{
    /**
     * @var array<string, class-string<PollingMethodDefinition>|PollingMethodDefinition>
     */
    private array $methods = [];

    /**
     * Register a polling method definition.
     *
     * @param  class-string<PollingMethodDefinition>|PollingMethodDefinition  $definition
     */
    public function register(PollingMethodType $type, string|PollingMethodDefinition $definition): self
    {
        $this->methods[$type->value] = $definition;

        return $this;
    }

    /**
     * Determine if a polling method is registered.
     */
    public function has(PollingMethodType $type): bool
    {
        return isset($this->methods[$type->value]);
    }

    /**
     * Get the definition for a polling method.
     */
    public function get(PollingMethodType $type): ?PollingMethodDefinition
    {
        $definition = $this->methods[$type->value] ?? null;

        if ($definition === null) {
            return null;
        }

        if (is_string($definition)) {
            $this->methods[$type->value] = app($definition);
        }

        return $this->methods[$type->value];
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
     * Get all registered definitions.
     *
     * @return array<string, PollingMethodDefinition>
     */
    public function all(): array
    {
        $resolved = [];
        foreach (array_keys($this->methods) as $key) {
            $type = PollingMethodType::tryFrom($key);
            if ($type !== null) {
                $resolved[$key] = $this->get($type);
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
        foreach (array_keys($this->methods) as $key) {
            $type = PollingMethodType::tryFrom($key);
            if ($type !== null) {
                $types[] = $type;
            }
        }

        return $types;
    }
}
