<?php

namespace LibreNMS\Enum;

use App\Facades\LibrenmsConfig;

/**
 * Device types an OS definition may set, must match the type enum in os_schema.json
 */
enum DeviceType: string
{
    case Audiovisual = 'audiovisual';
    case Collaboration = 'collaboration';
    case Environment = 'environment';
    case Firewall = 'firewall';
    case Iot = 'iot';
    case LoadBalancer = 'loadbalancer';
    case Management = 'management';
    case Network = 'network';
    case Optical = 'optical';
    case Power = 'power';
    case Printer = 'printer';
    case Security = 'security';
    case Server = 'server';
    case Storage = 'storage';
    case Surveillance = 'surveillance';
    case Timing = 'timing';
    case Voice = 'voice';
    case Wireless = 'wireless';
    case Workstation = 'workstation';

    public function label(): string
    {
        return __('device.types.' . $this->value);
    }

    /**
     * Configured icon per device type, keyed by type
     *
     * @return array<string, string>
     */
    public static function icons(): array
    {
        $icons = [];
        foreach (LibrenmsConfig::get('device_types', []) as $type => $icon) {
            // legacy list format: [['type' => 'x', 'icon' => 'y']]
            if (is_array($icon)) {
                if (isset($icon['type'], $icon['icon'])) {
                    $icons[$icon['type']] = $icon['icon'];
                }

                continue;
            }

            $icons[$type] = $icon;
        }

        return $icons;
    }

    /**
     * Display name for any type string, including user defined types
     */
    public static function labelFor(string $type): string
    {
        return self::tryFrom($type)?->label() ?? ucfirst($type);
    }
}
