<?php

namespace LibreNMS\Alerting;

final class AlertRuleDefinitions
{
    /**
     * @return array<int, array{name: string, builder: array<string, mixed>, extra: array<string, mixed>, default?: bool, notes?: string, severity?: string}>
     */
    public static function all(): array
    {
        $definitions = json_decode(file_get_contents(resource_path('definitions/alert_rules.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach ($definitions as $id => $definition) {
            $extra = $definition['extra'] ?? [];
            $definitions[$id]['extra'] = is_string($extra) ? json_decode($extra, true, flags: JSON_THROW_ON_ERROR) : $extra;
        }

        return $definitions;
    }
}
