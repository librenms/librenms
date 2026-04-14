<?php

use App\Models\WirelessSensor;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

if (! function_exists('librenms_wireless_subscriber_summary')) {
    function librenms_wireless_subscriber_summary(int $deviceId): array
    {
        static $cache = [];

        if (isset($cache[$deviceId])) {
            return $cache[$deviceId];
        }

        $all_wireless_sensors = WirelessSensor::where('device_id', $deviceId)
            ->where('sensor_deleted', 0)
            ->orderBy('sensor_class')
            ->orderBy('sensor_index')
            ->orderBy('sensor_descr')
            ->get()
            ->toArray();

        $subscriber_column_map = [
            'distance' => ['label' => 'Distance', 'order' => 10],
            'tx_rate' => ['label' => 'TX Rate', 'order' => 20],
            'rx_rate' => ['label' => 'RX Rate', 'order' => 30],
            'ul_rssi' => ['label' => 'UL RSSI', 'order' => 40],
            'dl_rssi' => ['label' => 'DL RSSI', 'order' => 50],
            'rssi' => ['label' => 'RSSI', 'order' => 60],
            'ul_snr' => ['label' => 'UL SNR', 'order' => 70],
            'dl_snr' => ['label' => 'DL SNR', 'order' => 80],
            'ul_mcs' => ['label' => 'UL MCS', 'order' => 90],
            'dl_mcs' => ['label' => 'DL MCS', 'order' => 100],
            'snr' => ['label' => 'SNR', 'order' => 110],
            'quality' => ['label' => 'Quality', 'order' => 120],
            'capacity' => ['label' => 'Capacity', 'order' => 130],
        ];

        $subscriber_rows = [];
        $subscriber_columns = [];

        foreach ($all_wireless_sensors as $sensor) {
            if (in_array($sensor['sensor_class'], ['clients', 'frequency', 'ap-count', 'channel', 'cell'], true)) {
                continue;
            }

            $index = (string) ($sensor['sensor_index'] ?? '');
            if ($index === '') {
                continue;
            }

            $type = strtolower((string) ($sensor['sensor_type'] ?? ''));
            $descr = trim((string) ($sensor['sensor_descr'] ?? ''));
            $lower_descr = strtolower($descr);

            $direction = null;
            if (str_contains($type, '-ul') || str_contains($lower_descr, ' ul ')) {
                $direction = 'ul';
            } elseif (str_contains($type, '-dl') || str_contains($lower_descr, ' dl ')) {
                $direction = 'dl';
            } elseif (str_contains($type, '-tx') || str_starts_with($lower_descr, 'tx ')) {
                $direction = 'tx';
            } elseif (str_contains($type, '-rx') || str_starts_with($lower_descr, 'rx ')) {
                $direction = 'rx';
            }

            $column_key = match ($sensor['sensor_class']) {
                'distance' => 'distance',
                'quality' => 'quality',
                'capacity' => 'capacity',
                'rate' => $direction === 'tx' ? 'tx_rate' : ($direction === 'rx' ? 'rx_rate' : 'rate'),
                'mcs' => $direction === 'ul' ? 'ul_mcs' : ($direction === 'dl' ? 'dl_mcs' : 'mcs'),
                'rssi' => $direction === 'ul' ? 'ul_rssi' : ($direction === 'dl' ? 'dl_rssi' : 'rssi'),
                'snr' => $direction === 'ul' ? 'ul_snr' : ($direction === 'dl' ? 'dl_snr' : 'snr'),
                default => null,
            };

            if ($column_key === null || ! isset($subscriber_column_map[$column_key])) {
                continue;
            }

            $label = $descr;
            if (preg_match('/^(RX|TX|SNR|Sta)\s+\((.+)\)$/i', $descr, $matches)) {
                $label = trim($matches[2]);
            } else {
                $label = trim((string) preg_replace([
                    '/\s+(UL|DL)\s+RSSI$/i',
                    '/\s+(UL|DL)\s+SNR$/i',
                    '/\s+(UL|DL)\s+MCS$/i',
                    '/\s+(RX|TX)\s+Rate$/i',
                    '/\s+Tx\s+Quality$/i',
                    '/\s+Tx\s+Capacity$/i',
                    '/\s+Distance$/i',
                ], '', $descr));
            }

            $subscriber_rows[$index] ??= ['label' => 'Subscriber ' . $index, 'values' => []];
            if ($label !== '' && ($subscriber_rows[$index]['label'] === 'Subscriber ' . $index || strlen($label) > strlen($subscriber_rows[$index]['label']))) {
                $subscriber_rows[$index]['label'] = $label;
            }

            $current = $sensor['sensor_current'];
            $formatted_value = match ($sensor['sensor_class']) {
                'distance' => $current === null || $current === '' ? '' : Number::formatSi((float) $current * 1000, 3, 0, 'm'),
                'rate' => $current === null || $current === '' ? '' : Number::formatSi((float) $current, 3, 0, 'bps'),
                'mcs' => $current === null || $current === '' ? '' : (string) ((int) $current),
                'rssi' => $current === null || $current === '' ? '' : ((int) $current) . ' dBm',
                'snr' => $current === null || $current === '' ? '' : ((int) $current) . ' dB',
                'quality', 'capacity' => $current === null || $current === '' ? '' : ((int) $current) . '%',
                default => (string) $current,
            };

            $subscriber_rows[$index]['values'][$column_key] = [
                'text' => $formatted_value,
                'html' => $formatted_value,
            ];

            if ($formatted_value !== '' && ! empty($sensor['sensor_id'])) {
                $graph_type = 'wireless_' . $sensor['sensor_class'];
                $graph_title = addslashes(htmlentities($label . ' - ' . $subscriber_column_map[$column_key]['label']));
                $content = '<div class=list-large>' . $graph_title . '</div>';
                $content .= "<div style='width: 850px'>";

                foreach ([
                    \Carbon\Carbon::now()->subDay()->timestamp,
                    \Carbon\Carbon::now()->subWeek()->timestamp,
                    \Carbon\Carbon::now()->subMonth()->timestamp,
                    \Carbon\Carbon::now()->subYear()->timestamp,
                ] as $from) {
                    $content .= \LibreNMS\Util\Url::graphTag([
                        'type' => $graph_type,
                        'legend' => 'yes',
                        'height' => 100,
                        'width' => 340,
                        'to' => \Carbon\Carbon::now()->timestamp,
                        'from' => $from,
                        'id' => $sensor['sensor_id'],
                    ]);
                }

                $content .= '</div>';

                $subscriber_rows[$index]['values'][$column_key]['html'] = Url::overlibLink(
                    Url::deviceUrl($deviceId, ['tab' => 'wireless', 'metric' => $sensor['sensor_class']]),
                    $formatted_value,
                    $content
                );
            }

            $subscriber_columns[$column_key] = $subscriber_column_map[$column_key];
        }

        uasort($subscriber_rows, static fn ($left, $right) => strnatcasecmp($left['label'], $right['label']));
        uasort($subscriber_columns, static fn ($left, $right) => $left['order'] <=> $right['order']);

        return $cache[$deviceId] = [
            'rows' => $subscriber_rows,
            'columns' => $subscriber_columns,
            'has_data' => ! empty($subscriber_rows) && ! empty($subscriber_columns),
        ];
    }
}

if (! function_exists('librenms_render_wireless_subscriber_summary')) {
    function librenms_render_wireless_subscriber_summary(array $summary, string $title = 'Subscribers'): void
    {
        if (empty($summary['has_data'])) {
            return;
        }

        echo '<div class="panel panel-default">';
        echo '<div class="panel-heading"><h3 class="panel-title">' . htmlspecialchars($title) . '</h3></div>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover table-condensed table-striped">';
        echo '<thead><tr><th>Subscriber</th>';

        foreach ($summary['columns'] as $column) {
            echo '<th>' . htmlspecialchars((string) $column['label']) . '</th>';
        }

        echo '</tr></thead><tbody>';

        foreach ($summary['rows'] as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars((string) $row['label']) . '</td>';

            foreach (array_keys($summary['columns']) as $column_key) {
                $value = $row['values'][$column_key] ?? ['text' => '', 'html' => ''];
                echo '<td>' . ($value['html'] ?: htmlspecialchars((string) $value['text'])) . '</td>';
            }

            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div></div>';
    }
}
