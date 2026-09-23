<?php

namespace LibreNMS\Polling\Method;

use InvalidArgumentException;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Methods\PollingMethod;

class PollingMethodRegistry
{
    /**
     * @var array<string, class-string<PollingMethod>>
     */
    private array $methods = [];

    /**
     * Register a polling method class.
     *
     * @param  class-string<PollingMethod>  $methodClass
     */
    public function register(PollingMethodType $type, string $methodClass): self
    {
        $this->methods[$type->value] = $methodClass;

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
     * Get the polling method instance for a type.
     */
    public function get(PollingMethodType $type): ?PollingMethod
    {
        if (! isset($this->methods[$type->value])) {
            return null;
        }

        return resolve($this->methods[$type->value]);
    }

    /**
     * Get the polling method instance for a type, or throw an exception if not found.
     *
     * @throws InvalidArgumentException
     */
    public function require(PollingMethodType $type): PollingMethod
    {
        $method = $this->get($type);

        if ($method === null) {
            throw new InvalidArgumentException("Unknown polling method type: {$type->value}");
        }

        return $method;
    }

    /**
     * Get the default affects availability value for a polling method.
     */
    public function defaultAffectsAvailability(PollingMethodType $type): bool
    {
        return $this->require($type)->defaultAffectsAvailability();
    }

    /**
     * Get all registered polling methods.
     *
     * @return array<string, PollingMethod>
     */
    public function all(): array
    {
        $resolved = [];
        foreach (array_keys($this->methods) as $key) {
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
        foreach (array_keys($this->methods) as $key) {
            $type = PollingMethodType::tryFrom($key);
            if ($type !== null) {
                $types[] = $type;
            }
        }

        return $types;
    }
}
