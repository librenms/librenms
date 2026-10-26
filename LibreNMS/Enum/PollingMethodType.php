<?php

namespace LibreNMS\Enum;

enum PollingMethodType: string
{
    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';
    /** Stored types this version does not know, such as ones from a development branch */
    case Unknown = 'unknown';

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

    public function label(): string
    {
        return __('poller.methods.' . $this->value);
    }
}
