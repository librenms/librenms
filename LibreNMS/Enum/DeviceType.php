<?php

namespace LibreNMS\Enum;

/**
 * Device types an OS definition may set, must match the type enum in os_schema.json
 */
enum DeviceType: string
{
    case Camera = 'camera';
    case Collaboration = 'collaboration';
    case Environment = 'environment';
    case Firewall = 'firewall';
    case Iot = 'iot';
    case LoadBalancer = 'loadbalancer';
    case Management = 'management';
    case Media = 'media';
    case Network = 'network';
    case Optical = 'optical';
    case Other = 'other';
    case Power = 'power';
    case Printer = 'printer';
    case Security = 'security';
    case Server = 'server';
    case Storage = 'storage';
    case Timing = 'timing';
    case Voice = 'voice';
    case Wireless = 'wireless';
    case Workstation = 'workstation';

    public function label(): string
    {
        return __('device.types.' . $this->value);
    }

    /**
     * Display name for any type string, including user defined types
     */
    public static function labelFor(string $type): string
    {
        return self::tryFrom($type)?->label() ?? ucfirst($type);
    }
}
