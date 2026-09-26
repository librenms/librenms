<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;

/**
 * UI description of a polling method's settings: form fields, validation rules and icon.
 */
abstract class PollingMethodDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    abstract public function icon(): string;

    abstract protected function defaultConfig(): PollingMethodConfig;

    /**
     * @return array<string, mixed>
     */
    public function schemaDefaults(): array
    {
        return $this->defaultConfig()->settingsArray();
    }
}
