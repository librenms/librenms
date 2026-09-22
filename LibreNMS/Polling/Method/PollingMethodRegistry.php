<?php

namespace LibreNMS\Polling\Method;

use App\View\FieldSchema\HasFieldSchema;
use InvalidArgumentException;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;
use LibreNMS\Polling\Method\Probe\PollingMethodProbe;

class PollingMethodRegistry
{
    /**
     * @var array<string, array{
     *     configClass: class-string<PollingMethodConfig>,
     *     probeClass: class-string<PollingMethodProbe>,
     *     definitionClass: ?class-string<PollingMethodDefinition>,
     *     secretDefinitionClass: ?class-string<HasFieldSchema>,
     * }>
     */
    private array $methods = [];

    /**
     * @var array<string, PollingMethodDefinition>
     */
    private array $resolvedDefinitions = [];

    /**
     * @var array<string, ?HasFieldSchema>
     */
    private array $resolvedSecretDefinitions = [];

    /**
     * Register a polling method with its class-strings.
     *
     * @param  class-string<PollingMethodConfig>  $configClass
     * @param  class-string<PollingMethodProbe>  $probeClass
     * @param  class-string<PollingMethodDefinition>|null  $definitionClass
     * @param  class-string<HasFieldSchema>|null  $secretDefinitionClass
     */
    public function register(
        PollingMethodType $type,
        string $configClass,
        string $probeClass,
        ?string $definitionClass = null,
        ?string $secretDefinitionClass = null,
    ): self {
        $this->methods[$type->value] = [
            'configClass' => $configClass,
            'probeClass' => $probeClass,
            'definitionClass' => $definitionClass,
            'secretDefinitionClass' => $secretDefinitionClass,
        ];

        unset($this->resolvedDefinitions[$type->value]);
        unset($this->resolvedSecretDefinitions[$type->value]);

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
    public function definition(PollingMethodType $type): ?PollingMethodDefinition
    {
        if (! isset($this->methods[$type->value])) {
            return null;
        }

        if (! isset($this->resolvedDefinitions[$type->value])) {
            $definitionClass = $this->methods[$type->value]['definitionClass'];
            $this->resolvedDefinitions[$type->value] = $definitionClass !== null
                ? app($definitionClass)
                : new PollingMethodDefinition();
        }

        return $this->resolvedDefinitions[$type->value];
    }

    /**
     * Get the definition for a polling method, or throw an exception if not found.
     *
     * @throws InvalidArgumentException
     */
    public function require(PollingMethodType $type): PollingMethodDefinition
    {
        $definition = $this->definition($type);

        if ($definition === null) {
            throw new InvalidArgumentException("Unknown polling method type: {$type->value}");
        }

        return $definition;
    }

    /**
     * Get the config class for a polling method.
     *
     * @return class-string<PollingMethodConfig>
     */
    public function configClass(PollingMethodType $type): string
    {
        if (! isset($this->methods[$type->value])) {
            throw new InvalidArgumentException("Unknown polling method type: {$type->value}");
        }

        return $this->methods[$type->value]['configClass'];
    }

    /**
     * Get the probe for a polling method.
     */
    public function probe(PollingMethodType $type): PollingMethodProbe
    {
        if (! isset($this->methods[$type->value])) {
            throw new InvalidArgumentException("Unknown polling method type: {$type->value}");
        }

        $probeClass = $this->methods[$type->value]['probeClass'];

        return app($probeClass);
    }

    /**
     * Get the secret definition schema for a polling method, if one is defined.
     */
    public function secretDefinition(PollingMethodType $type): ?HasFieldSchema
    {
        if (! isset($this->methods[$type->value])) {
            return null;
        }

        if (! array_key_exists($type->value, $this->resolvedSecretDefinitions)) {
            $secretDefinitionClass = $this->methods[$type->value]['secretDefinitionClass'];
            $this->resolvedSecretDefinitions[$type->value] = $secretDefinitionClass !== null
                ? app($secretDefinitionClass)
                : null;
        }

        return $this->resolvedSecretDefinitions[$type->value];
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
        foreach (array_keys($this->methods) as $key) {
            $type = PollingMethodType::tryFrom($key);
            if ($type !== null) {
                $resolved[$key] = $this->definition($type);
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
