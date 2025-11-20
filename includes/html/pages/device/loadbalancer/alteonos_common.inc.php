<?php

use App\Models\Sensor;

if (! function_exists('alteonos_sensor_types')) {
    function alteonos_sensor_types(): array
    {
        return [
            'alteonos_real_servers' => [
                'slbEnhRealServer',
                'slbRealServer',
            ],
            'alteonos_real_groups' => [
                'slbOperEnhGroupRealServerRuntime',
                'slbOperEnhGroupRealServerRuntimeStatus',
                'slbOperGroupRealServer',
                'slbOperGroupRealServerState',
            ],
            'alteonos_virtual_servers' => [
                'slbCurCfgEnhVirtServer',
                'slbCurCfgVirtServer',
            ],
            'alteonos_virtual_services' => [
                'slbVirtServices',
                'slbVirtServicesInfo',
                'slbVirtServicesInfoState',
                'slbCurCfgEnhVirtService',
                'slbCurCfgEnhVirtServiceStatus',
                'slbCurCfgVirtService',
                'slbCurCfgVirtServiceStatus',
            ],
        ];
    }
}

if (! function_exists('alteonos_sensor_types_for_tab')) {
    function alteonos_sensor_types_for_tab(string $tab): array
    {
        $types = alteonos_sensor_types();

        return $types[$tab] ?? [];
    }
}

if (! function_exists('alteonos_parse_state_value')) {
    function alteonos_parse_state_value($value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            if (preg_match('/(-?\d+)(?!.*\d)/', $value, $m)) {
                return (int) $m[1];
            }
        }

        return null;
    }
}

if (! function_exists('alteonos_state_lookup')) {
    function alteonos_state_lookup(string $sensorType, $value): ?array
    {
        $maps = [
            'slbOperEnhGroupRealServerRuntime' => [
                1 => ['descr' => 'running', 'generic' => 0],
                2 => ['descr' => 'failed', 'generic' => 2],
                3 => ['descr' => 'disabled', 'generic' => 1],
                4 => ['descr' => 'overloaded', 'generic' => 1],
            ],
            'slbOperEnhGroupRealServerRuntimeStatus' => [
                1 => ['descr' => 'running', 'generic' => 0],
                2 => ['descr' => 'failed', 'generic' => 2],
                3 => ['descr' => 'disabled', 'generic' => 1],
                4 => ['descr' => 'overloaded', 'generic' => 1],
            ],
            'slbOperGroupRealServer' => [
                1 => ['descr' => 'enable', 'generic' => 0],
                2 => ['descr' => 'disable', 'generic' => 1],
                3 => ['descr' => 'shutdown-connection', 'generic' => 2],
                4 => ['descr' => 'shutdown-persistent-sessions', 'generic' => 2],
            ],
            'slbOperGroupRealServerState' => [
                1 => ['descr' => 'enable', 'generic' => 0],
                2 => ['descr' => 'disable', 'generic' => 1],
                3 => ['descr' => 'shutdown-connection', 'generic' => 2],
                4 => ['descr' => 'shutdown-persistent-sessions', 'generic' => 2],
            ],
            'slbCurCfgEnhVirtService' => [
                1 => ['descr' => 'up', 'generic' => 0],
                2 => ['descr' => 'down', 'generic' => 2],
                3 => ['descr' => 'adminDown', 'generic' => 1],
                4 => ['descr' => 'warning', 'generic' => 1],
                5 => ['descr' => 'shutdown', 'generic' => 2],
                6 => ['descr' => 'error', 'generic' => 2],
            ],
            'slbCurCfgEnhVirtServiceStatus' => [
                1 => ['descr' => 'up', 'generic' => 0],
                2 => ['descr' => 'down', 'generic' => 2],
                3 => ['descr' => 'adminDown', 'generic' => 1],
                4 => ['descr' => 'warning', 'generic' => 1],
                5 => ['descr' => 'shutdown', 'generic' => 2],
                6 => ['descr' => 'error', 'generic' => 2],
            ],
            'slbCurCfgVirtService' => [
                1 => ['descr' => 'up', 'generic' => 0],
                2 => ['descr' => 'down', 'generic' => 2],
                3 => ['descr' => 'adminDown', 'generic' => 1],
                4 => ['descr' => 'warning', 'generic' => 1],
                5 => ['descr' => 'shutdown', 'generic' => 2],
                6 => ['descr' => 'error', 'generic' => 2],
            ],
            'slbCurCfgVirtServiceStatus' => [
                1 => ['descr' => 'up', 'generic' => 0],
                2 => ['descr' => 'down', 'generic' => 2],
                3 => ['descr' => 'adminDown', 'generic' => 1],
                4 => ['descr' => 'warning', 'generic' => 1],
                5 => ['descr' => 'shutdown', 'generic' => 2],
                6 => ['descr' => 'error', 'generic' => 2],
            ],
            'slbVirtServices' => [
                1 => ['descr' => 'blocked', 'generic' => 1],
                2 => ['descr' => 'running', 'generic' => 0],
                3 => ['descr' => 'failed', 'generic' => 2],
                4 => ['descr' => 'disabled', 'generic' => 1],
                5 => ['descr' => 'slowstart', 'generic' => 1],
                6 => ['descr' => 'overflow', 'generic' => 1],
                7 => ['descr' => 'noinstance', 'generic' => 2],
                2147483647 => ['descr' => 'unsupported', 'generic' => 3],
            ],
            'slbVirtServicesInfo' => [
                1 => ['descr' => 'blocked', 'generic' => 1],
                2 => ['descr' => 'running', 'generic' => 0],
                3 => ['descr' => 'failed', 'generic' => 2],
                4 => ['descr' => 'disabled', 'generic' => 1],
                5 => ['descr' => 'slowstart', 'generic' => 1],
                6 => ['descr' => 'overflow', 'generic' => 1],
                7 => ['descr' => 'noinstance', 'generic' => 2],
                2147483647 => ['descr' => 'unsupported', 'generic' => 3],
            ],
            'slbVirtServicesInfoState' => [
                1 => ['descr' => 'blocked', 'generic' => 1],
                2 => ['descr' => 'running', 'generic' => 0],
                3 => ['descr' => 'failed', 'generic' => 2],
                4 => ['descr' => 'disabled', 'generic' => 1],
                5 => ['descr' => 'slowstart', 'generic' => 1],
                6 => ['descr' => 'overflow', 'generic' => 1],
                7 => ['descr' => 'noinstance', 'generic' => 2],
                2147483647 => ['descr' => 'unsupported', 'generic' => 3],
            ],
        ];

        $intVal = alteonos_parse_state_value($value);
        if ($intVal === null) {
            return null;
        }

        return $maps[$sensorType][$intVal] ?? null;
    }
}

if (! function_exists('alteonos_loadbalancer_fetch')) {
    function alteonos_loadbalancer_fetch(array $device, string $tab): array
    {
        $deviceId = (int) ($device['device_id'] ?? 0);
        $types = alteonos_sensor_types_for_tab($tab);

        if ($deviceId <= 0 || empty($types)) {
            return [];
        }

        return Sensor::query()
            ->with('translations')
            ->where('device_id', $deviceId)
            ->where('sensor_class', 'state')
            ->whereIn('sensor_type', $types)
            ->orderBy('sensor_descr')
            ->get()
            ->map(function (Sensor $sensor) {
                $translation = $sensor->translations->firstWhere('state_value', $sensor->sensor_current);
                $stateDescr = $translation->state_descr ?? null;
                $stateValue = $translation->state_value ?? null;
                $stateGeneric = $translation->state_generic_value ?? null;

                $needsFallback = $stateDescr === null || strtolower($stateDescr) === 'unknown';
                if ($needsFallback) {
                    if ($fallback = alteonos_state_lookup($sensor->sensor_type, $sensor->sensor_current)) {
                        $stateDescr = $fallback['descr'];
                        $stateGeneric = $fallback['generic'];
                        $stateValue = $sensor->sensor_current;
                    }
                }

                $row = [
                    'sensor_id' => $sensor->sensor_id,
                    'sensor_descr' => $sensor->sensor_descr,
                    'sensor_current' => $sensor->sensor_current,
                    'sensor_prev' => $sensor->sensor_prev,
                    'sensor_limit' => $sensor->sensor_limit,
                    'sensor_limit_low' => $sensor->sensor_limit_low,
                    'sensor_index' => $sensor->sensor_index,
                    'lastupdate' => $sensor->lastupdate,
                    'state_descr' => $stateDescr,
                    'state_value' => $stateValue,
                    'state_generic_value' => $stateGeneric,
                ];

                $row['sensor_descr'] = alteonos_normalize_description($row);

                return $row;
            })
            ->all();
    }
}

if (! function_exists('alteonos_state_class')) {
    function alteonos_state_class(?int $generic): string
    {
        return match ($generic) {
            0 => 'text-success',
            1 => 'text-warning',
            2 => 'text-danger',
            default => 'text-muted',
        };
    }
}

if (! function_exists('alteonos_render_sensor_table')) {
    function alteonos_render_sensor_table(string $heading, array $rows): void
    {
        echo '<h3>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h3>';

        if (empty($rows)) {
            echo '<div class="alert alert-info" role="alert">' . htmlspecialchars(__('No data available.'), ENT_QUOTES, 'UTF-8') . '</div>';

            return;
        }

        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-condensed">';
        echo '<thead><tr>';
        echo '<th>' . htmlspecialchars(__('Description'), ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th>' . htmlspecialchars(__('State'), ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th>' . htmlspecialchars(__('Value'), ENT_QUOTES, 'UTF-8') . '</th>';
        echo '<th>' . htmlspecialchars(__('Last Updated'), ENT_QUOTES, 'UTF-8') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($rows as $row) {
            $descr = nl2br(htmlspecialchars((string) ($row['sensor_descr'] ?? ''), ENT_QUOTES, 'UTF-8'));
            $statusText = $row['state_descr'] ?? ('Value ' . ($row['sensor_current'] ?? ''));
            $statusClass = alteonos_state_class(isset($row['state_generic_value']) ? (int) $row['state_generic_value'] : null);
            $value = htmlspecialchars((string) ($row['sensor_current'] ?? ''), ENT_QUOTES, 'UTF-8');
            $updated = htmlspecialchars((string) ($row['lastupdate'] ?? ''), ENT_QUOTES, 'UTF-8');

            echo '<tr>';
            echo '<td class="small">' . $descr . '</td>';
            echo '<td><span class="' . $statusClass . '">' . htmlspecialchars((string) $statusText, ENT_QUOTES, 'UTF-8') . '</span></td>';
            echo '<td>' . $value . '</td>';
            echo '<td class="text-nowrap">' . $updated . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }
}

if (! function_exists('alteonos_normalize_description')) {
    function alteonos_normalize_description(array $row): string
    {
        $descr = (string) ($row['sensor_descr'] ?? '');
        $type = (string) ($row['sensor_type'] ?? '');

        return alteonos_strip_multiindex($descr, $type);
    }
}

if (! function_exists('alteonos_strip_multiindex')) {
    function alteonos_strip_multiindex(string $descr, string $sensorType): string
    {
        $map = [
            'slbRealServer' => 'Real Server',
            'slbEnhRealServer' => 'Real Server',
            'slbStatRealServer' => 'Real Server',
            'slbStatEnhRServer' => 'Real Server',
            'slbCurCfgEnhVirtServer' => 'Virt Server',
            'slbCurCfgVirtServer' => 'Virt Server',
            'slbOperEnhGroupRealServerRuntime' => 'Real Server Group',
            'slbOperEnhGroupRealServerRuntimeStatus' => 'Real Server Group',
            'slbOperGroupRealServer' => 'Real Server Group',
            'slbOperGroupRealServerState' => 'Real Server Group',
            'slbStatEnhGroup' => 'Real Server Group',
            'slbStatGroup' => 'Real Server Group',
        ];

        foreach ($map as $typePrefix => $label) {
            if (stripos($sensorType, $typePrefix) === 0) {
                $pattern = '/^(' . preg_quote($label, '/') . '\s+)(\d+)(?:\.\d+)+/i';
                $descr = preg_replace($pattern, '$1$2', $descr);
                break;
            }
        }

        return $descr;
    }
}
