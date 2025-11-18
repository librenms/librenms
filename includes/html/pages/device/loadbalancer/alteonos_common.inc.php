<?php

if (! function_exists('alteonos_sensor_types')) {
    function alteonos_sensor_types(): array
    {
        return [
            'alteonos_real_servers' => [
                'slbEnhRealServer',
                'slbRealServer',
            ],
            'alteonos_real_groups' => [
                'slbOperEnhGroupRealServerRuntimeStatus',
                'slbOperGroupRealServer',
            ],
            'alteonos_virtual_servers' => [
                'slbCurCfgEnhVirtServer',
                'slbCurCfgVirtServer',
            ],
            'alteonos_virtual_services' => [
                'slbVirtServices',
                'slbCurCfgEnhVirtServiceStatus',
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
            'slbCurCfgEnhVirtServiceStatus' => [
                1 => ['descr' => 'up', 'generic' => 0],
                2 => ['descr' => 'down', 'generic' => 2],
                3 => ['descr' => 'adminDown', 'generic' => 1],
                4 => ['descr' => 'warning', 'generic' => 1],
                5 => ['descr' => 'shutdown', 'generic' => 2],
                6 => ['descr' => 'error', 'generic' => 2],
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

        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $params = array_merge([$deviceId, 'state'], $types);

        $sql = 'SELECT s.sensor_id, s.sensor_descr, s.sensor_current, s.sensor_prev, s.sensor_limit, '
            . 's.sensor_limit_low, s.sensor_index, s.sensor_type, s.lastupdate, '
            . 'st.state_descr, st.state_value, st.state_generic_value '
            . 'FROM sensors AS s '
            . 'LEFT JOIN sensors_to_state_indexes AS sidx ON s.sensor_id = sidx.sensor_id '
            . 'LEFT JOIN state_translations AS st '
            . 'ON sidx.state_index_id = st.state_index_id AND st.state_value = s.sensor_current '
            . 'WHERE s.device_id = ? AND s.sensor_class = ? AND s.sensor_type IN (' . $placeholders . ') '
            . 'ORDER BY s.sensor_descr';

        return dbFetchRows($sql, $params);
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
