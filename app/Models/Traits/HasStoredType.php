<?php

namespace App\Models\Traits;

use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;

/**
 * For models with a type column that may hold types this version does not know.
 */
trait HasStoredType
{
    abstract protected function storedTypeColumn(): string;

    public function typeName(): string
    {
        return (string) ($this->attributes[$this->storedTypeColumn()] ?? '');
    }

    public function typeLabel(): string
    {
        /** @var PollingMethodType|SecretType|null $type */
        $type = $this->getAttribute($this->storedTypeColumn());

        return $type?->isUnknown() ? __('poller.unknown_type', ['type' => $this->typeName()]) : (string) $type?->label();
    }
}
