<?php

namespace LibreNMS\Interfaces;

use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Secrets\SecretData;

/**
 * @template-covariant T of PollingMethodConfigInterface
 */
interface PollingMethodDefinitionInterface extends HasFieldSchema
{
    /**
     * Get the icon name for this method type
     */
    public function icon(): string;

    /**
     * @return class-string<T>
     */
    public function class(): string;

    /**
     * Default affects_availability flag for this method type.
     */
    public function defaultAffectsAvailability(): bool;

    /**
     * @return SecretDefinitionInterface<SecretData>|null
     */
    public function secretDefinition(): ?SecretDefinitionInterface;
}
