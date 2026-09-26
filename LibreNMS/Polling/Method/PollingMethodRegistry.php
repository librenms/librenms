<?php

namespace LibreNMS\Polling\Method;

use InvalidArgumentException;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;
use LibreNMS\Polling\Method\Methods\PollingMethod;

class PollingMethodRegistry
{
    /**
     * @var array<string, array{method: class-string<PollingMethod>, definition: class-string<PollingMethodDefinition>}>
     */
    private array $methods = [];

    /**
     * Register a polling method and the definition describing its settings.
     *
     * @param  class-string<PollingMethod>  $methodClass
     * @param  class-string<PollingMethodDefinition>  $definitionClass
     */
    public function register(PollingMethodType $type, string $methodClass, string $definitionClass): self
    {
        $this->methods[$type->value] = ['method' => $methodClass, 'definition' => $definitionClass];

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

        return resolve($this->methods[$type->value]['method']);
    }

    /**
     * Get the polling method instance for a type, or throw an exception if not found.
     *
     * @throws InvalidArgumentException
     */
    public function require(PollingMethodType $type): PollingMethod
    {
        return $this->get($type) ?? throw new InvalidArgumentException("Unknown polling method type: {$type->value}");
    }

    /**
     * Get the settings definition (form fields, rules) for a type.
     *
     * @throws InvalidArgumentException
     */
    public function definition(PollingMethodType $type): PollingMethodDefinition
    {
        if (! isset($this->methods[$type->value])) {
            throw new InvalidArgumentException("Unknown polling method type: {$type->value}");
        }

        return resolve($this->methods[$type->value]['definition']);
    }

    /**
     * Get all registered polling method types.
     *
     * @return array<PollingMethodType>
     */
    public function types(): array
    {
        return array_map(PollingMethodType::from(...), array_keys($this->methods));
    }
}
