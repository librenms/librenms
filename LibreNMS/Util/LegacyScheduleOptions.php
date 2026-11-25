<?php

namespace LibreNMS\Util;

use App\Facades\LibrenmsConfig;
use App\Models\Notification;

class LegacyScheduleOptions
{
    private const array OPTIONS = [
        'service_alerting_enabled',
        'service_billing_enabled',
        'service_discovery_enabled',
        'service_ping_enabled',
        'service_poller_enabled',
        'service_services_enabled',
    ];

    /**
     * @return list<string>
     */
    public static function configured(): array
    {
        $config = LibrenmsConfig::getAll();

        return array_values(array_filter(self::OPTIONS, fn (string $option): bool => array_key_exists($option, $config)));
    }

    public static function notify(): void
    {
        $options = self::configured();
        if ($options === []) {
            return;
        }

        $node = (string) (config('librenms.node_id') ?: php_uname('n'));

        Notification::firstOrCreate([
            'checksum' => hash('sha512', 'legacy-schedule-options:' . $node),
        ], [
            'title' => 'Legacy scheduling options on ' . $node,
            'body' => 'Legacy scheduling options are explicitly configured: ' . implode(', ', $options) . '. Review the corresponding schedule_type settings before removing these options; removing them may change how tasks are scheduled.',
            'severity' => 1,
            'source' => 'configuration',
            'datetime' => now(),
        ]);
    }
}
