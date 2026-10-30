<?php

namespace LibreNMS\Enum;

/**
 * Device types an OS definition may set, must match the type enum in os_schema.json
 */
enum DeviceType: string
{
    case Appliance = 'appliance';
    case Camera = 'camera';
    case Collaboration = 'collaboration';
    case Encoder = 'encoder';
    case Environment = 'environment';
    case Environmental = 'environmental';
    case Firewall = 'firewall';
    case LoadBalancer = 'loadbalancer';
    case Management = 'management';
    case Network = 'network';
    case Optical = 'optical';
    case Power = 'power';
    case Printer = 'printer';
    case Proxy = 'proxy';
    case Sensor = 'sensor';
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
