<?php

namespace LibreNMS\Traits;

/**
 * For type enums with an Unknown case: stored types this version does not know, such as ones from a development branch.
 */
trait HasUnknownCase
{
    public static function fromStored(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }

    public static function tryFromKnown(string $value): ?self
    {
        $type = self::tryFrom($value);

        return $type === self::Unknown ? null : $type;
    }

    /**
     * @return list<self>
     */
    public static function known(): array
    {
        return array_values(array_filter(self::cases(), fn (self $type): bool => $type !== self::Unknown));
    }

    public function isUnknown(): bool
    {
        return $this === self::Unknown;
    }
}
