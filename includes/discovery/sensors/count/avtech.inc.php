<?php

/**
 * avtech.inc.php
 *
 * LibreNMS particulate matter (PM2.5/PM10) discovery for AVTECH Room Alert devices
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS Contributors
 */
if (isset($pre_cache['ramax-channels'])) {
    foreach ($pre_cache['ramax-channels'] as $index => $channel) {
        $type = $channel[3] ?? '';
        $value = $channel[4] ?? null;
        $label = $channel['label'] ?? '';

        if (in_array($type, ['PM2.5', 'PM10']) && $value !== null) {
            $oid = '.1.3.6.1.4.1.20916.1.14.3.1.1.4.' . $index;
            $sensor_index = 'ramax-' . md5((string) $index);
            $descr = trim($label . ' ' . $type, ' ');
            discover_sensor(
                null, 'count', $device,
                $oid, $sensor_index, 'avtech',
                $descr, 100, 1,
                null, null, null, null,
                $value
            );
        }
    }
}
